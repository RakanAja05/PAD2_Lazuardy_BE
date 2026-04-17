<?php

namespace App\Services\Tutors;

use App\DTOs\ResponseDTO;
use App\Enums\TutorStatusEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class FindTutorService
{
    public function search(Request $request): ResponseDTO
    {
        $user = Auth::user();

        if (!$user->latitude || !$user->longitude) {
            return new ResponseDTO([
                'status' => 'error',
                'message' => 'Lokasi Anda belum tersedia. Mohon aktifkan GPS atau lengkapi data alamat.',
                'errors' => [
                    'location' => 'missing',
                ],
            ], 400);
        }

        $lat = $user->latitude;
        $lng = $user->longitude;

        $radius = $request->input('radius', 10);
        $subjectId = $request->input('subject_id');
        $classId = $request->input('class_id');
        $minRating = $request->input('min_rating');
        $gender = $request->input('gender');

        $userProvince = null;
        if ($user->home_address && is_array($user->home_address)) {
            $userProvince = $user->home_address['province'] ?? null;
        } elseif ($user->home_address && is_string($user->home_address)) {
            $addressData = json_decode($user->home_address, true);
            $userProvince = $addressData['province'] ?? null;
        }

        $limit = 9;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $limit;

        $weightRating = $request->input('weight_rating', 0.6);
        $weightDistance = $request->input('weight_distance', 0.4);

        $weightRating = max(0, min(1, $weightRating));
        $weightDistance = max(0, min(1, $weightDistance));

        $maxRadiusKm = 50;
        $expandedRadius = min(((float) $radius) * 2, $maxRadiusKm);

        $hasCourseModeColumn = Schema::hasColumn('tutors', 'course_mode');

        $buildQuery = function (?string $province, float $radiusKm, bool $applyRadius, bool $onlineOnly) use (
            $lat,
            $lng,
            $gender,
            $subjectId,
            $classId,
            $minRating
        ) {
            $query = User::select('users.*')
                ->selectRaw("\n                    (6371 * acos(\n                        cos(radians(?)) * cos(radians(latitude)) *\n                        cos(radians(longitude) - radians(?)) +\n                        sin(radians(?)) * sin(radians(latitude))\n                    )) AS distance,\n                    AVG(reviews.rate) as avg_rating,\n                    COUNT(reviews.id) as review_count\n                ", [$lat, $lng, $lat])
                ->join('tutors', 'users.id', '=', 'tutors.user_id')
                ->leftJoin('reviews', 'users.id', '=', 'reviews.tutor_id')
                ->where('users.role', 'tutor')
                ->where('tutors.status', TutorStatusEnum::VERIFIED->value)
                ->groupBy('users.id');

            // Tahap 1 (coarse): filter berdasarkan province
            if ($province) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(users.home_address, '$.province')) = ?", [$province]);
            }

            if ($onlineOnly) {
                $query->where('tutors.course_mode', 'online');
            }

            if ($gender) {
                $query->where('users.gender', $gender);
            }

            if ($subjectId) {
                $query->join('tutor_subjects', 'tutors.user_id', '=', 'tutor_subjects.tutor_id')
                    ->where('tutor_subjects.subject_id', $subjectId);
            }

            if ($classId) {
                if (!$subjectId) {
                    $query->join('tutor_subjects', 'tutors.user_id', '=', 'tutor_subjects.tutor_id')
                        ->join('subjects', 'tutor_subjects.subject_id', '=', 'subjects.id')
                        ->where('subjects.class_id', $classId);
                } else {
                    $query->join('subjects', 'tutor_subjects.subject_id', '=', 'subjects.id')
                        ->where('subjects.class_id', $classId);
                }
            }

            // Tahap 2 (fine): filter berdasarkan radius
            if ($applyRadius) {
                $query->whereNotNull('users.latitude')
                    ->whereNotNull('users.longitude')
                    ->having('distance', '<=', $radiusKm);
            }

            if ($minRating) {
                $query->havingRaw('AVG(reviews.rate) >= ?', [$minRating]);
            }

            return $query->with(['tutor']);
        };

        // Strategi pencarian bertahap + fallback agar tidak kosong
        $attempts = [];

        // 1) Province + radius (default)
        $attempts[] = [
            'province' => $userProvince,
            'radius' => (float) $radius,
            'applyRadius' => true,
            'onlineOnly' => false,
            'strategy' => 'province+radius',
        ];

        // 2) Province + expanded radius
        if ($expandedRadius > (float) $radius) {
            $attempts[] = [
                'province' => $userProvince,
                'radius' => (float) $expandedRadius,
                'applyRadius' => true,
                'onlineOnly' => false,
                'strategy' => 'province+expanded_radius',
            ];
        }

        // 3) Drop province + radius
        if ($userProvince) {
            $attempts[] = [
                'province' => null,
                'radius' => (float) $radius,
                'applyRadius' => true,
                'onlineOnly' => false,
                'strategy' => 'no_province+radius',
            ];
        }

        // 4) Drop province + expanded radius
        if ($expandedRadius > (float) $radius) {
            $attempts[] = [
                'province' => null,
                'radius' => (float) $expandedRadius,
                'applyRadius' => true,
                'onlineOnly' => false,
                'strategy' => 'no_province+expanded_radius',
            ];
        }

        // 5) Online tutors fallback (hanya jika kolom course_mode ada)
        if ($hasCourseModeColumn) {
            $attempts[] = [
                'province' => null,
                'radius' => (float) $radius,
                'applyRadius' => false,
                'onlineOnly' => true,
                'strategy' => 'online_only',
            ];
        }

        $allTutors = collect();
        $effectiveRadius = (float) $radius;
        $effectiveProvince = $userProvince;
        $effectiveRadiusApplied = true;
        $effectiveOnlineOnly = false;
        $usedStrategy = 'province+radius';

        foreach ($attempts as $attempt) {
            $effectiveRadius = (float) $attempt['radius'];
            $effectiveProvince = $attempt['province'];
            $effectiveRadiusApplied = (bool) $attempt['applyRadius'];
            $effectiveOnlineOnly = (bool) $attempt['onlineOnly'];
            $usedStrategy = $attempt['strategy'];

            $query = $buildQuery(
                $attempt['province'],
                (float) $attempt['radius'],
                (bool) $attempt['applyRadius'],
                (bool) $attempt['onlineOnly']
            );

            $allTutors = $query->get();

            if ($allTutors->isNotEmpty()) {
                break;
            }
        }

        $effectiveRadiusForScore = $effectiveRadius > 0 ? $effectiveRadius : 1;

        $tutorsWithScore = $allTutors->map(function ($tutor) use ($effectiveRadiusForScore, $weightRating, $weightDistance) {
            $normalizedRating = ((float) ($tutor->avg_rating ?? 0)) / 5;

            $distanceKm = is_numeric($tutor->distance) ? (float) $tutor->distance : null;
            $normalizedDistance = $distanceKm === null
                ? 0
                : 1 - ($distanceKm / $effectiveRadiusForScore);

            $recommendationScore = ($normalizedRating * $weightRating) + ($normalizedDistance * $weightDistance);

            $tutor->recommendation_score = round($recommendationScore, 4);
            $tutor->normalized_rating = round($normalizedRating, 4);
            $tutor->normalized_distance = round($normalizedDistance, 4);

            return $tutor;
        });

        $tutorsWithScore = $tutorsWithScore->sortByDesc('recommendation_score')->values();

        $totalTutors = $tutorsWithScore->count();
        $totalPages = (int) ceil($totalTutors / $limit);

        $tutors = $tutorsWithScore->slice($offset, $limit)->values();

        $result = $tutors->map(function ($tutor, $index) use ($offset) {
            $address = is_string($tutor->home_address)
                ? json_decode($tutor->home_address, true)
                : $tutor->home_address;

            return [
                'rank' => $offset + $index + 1,
                'recommendation_score' => $tutor->recommendation_score,
                'score_breakdown' => [
                    'normalized_rating' => $tutor->normalized_rating,
                    'normalized_distance' => $tutor->normalized_distance,
                ],
                'user_id' => $tutor->id,
                'name' => $tutor->name,
                'profile_photo_path' => $tutor->profile_photo_path,
                'gender' => $tutor->gender,
                'distance' => round($tutor->distance, 2),
                'distance_text' => round($tutor->distance, 2) . ' km',
                'address' => [
                    'subdistrict' => $address['subdistrict'] ?? null,
                    'district' => $address['district'] ?? null,
                    'regency' => $address['regency'] ?? null,
                    'province' => $address['province'] ?? null,
                    'full_text' => implode(', ', array_filter([
                        $address['subdistrict'] ?? null,
                        $address['district'] ?? null,
                        $address['regency'] ?? null,
                    ])),
                ],
                'tutor_info' => [
                    'price' => $tutor->tutor->price ?? null,
                    'price_formatted' => $tutor->tutor->price ? 'Rp ' . number_format($tutor->tutor->price, 0, ',', '.') : null,
                    'experience' => $tutor->tutor->experience ?? null,
                    'badge' => $tutor->tutor->badge ?? null,
                    'status' => $tutor->tutor->status ?? null,
                ],
                'rating' => [
                    'average' => round($tutor->avg_rating ?? 0, 1),
                    'count' => (int) ($tutor->review_count ?? 0),
                    'stars_text' => round($tutor->avg_rating ?? 0, 1) . ' ⭐',
                ],
                'maps_url' => "https://www.google.com/maps?q={$tutor->latitude},{$tutor->longitude}",
                'is_verified' => !empty($tutor->tutor->badge),
            ];
        });

        return new ResponseDTO([
            'status' => 'success',
            'message' => 'Tutors sorted by recommendation score',
            'data' => [
                'tutors' => $result,
                'pagination' => [
                    'current_page' => $page,
                    'total' => $totalTutors,
                    'per_page' => $limit,
                    'total_pages' => $totalPages,
                    'has_more' => $page < $totalPages,
                    'next_page' => $page < $totalPages ? $page + 1 : null,
                ],
                'filters' => [
                    'radius_km' => $radius,
                    'effective_radius_km' => $effectiveRadius,
                    'subject_id' => $subjectId,
                    'class_id' => $classId,
                    'min_rating' => $minRating,
                    'gender' => $gender,
                ],
                'auto_optimizations' => [
                    'province_filter' => $effectiveProvince ? "Filtered to {$effectiveProvince} province" : 'No province filter applied',
                    'fallback_applied' => $usedStrategy !== 'province+radius',
                    'strategy' => $usedStrategy,
                ],
                'algorithm' => [
                    'description' => 'Recommendation score = (normalized_rating x weight_rating) + (normalized_distance x weight_distance)',
                    'weights' => [
                        'rating' => $weightRating,
                        'distance' => $weightDistance,
                    ],
                ],
                'meta' => [
                    'user_location' => [
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ],
                    'applied_filters' => [
                        'province' => $effectiveProvince,
                        'radius_km' => $effectiveRadius,
                        'radius_applied' => $effectiveRadiusApplied,
                        'online_only' => $effectiveOnlineOnly,
                    ],
                ],
            ],
        ], 200);
    }

    public function show(Request $request, $id): ResponseDTO
    {
        $user = Auth::user();

        if (!$user->latitude || !$user->longitude) {
            return new ResponseDTO([
                'status' => 'error',
                'message' => 'Lokasi Anda belum tersedia.',
                'errors' => [
                    'location' => 'missing',
                ],
            ], 400);
        }

        $lat = $user->latitude;
        $lng = $user->longitude;

        $tutor = User::select([
                'users.id',
                'users.name',
                'users.telephone_number',
                'users.profile_photo_path',
                'users.gender',
                'users.date_of_birth',
                'users.religion',
                'users.home_address',
                'users.latitude',
                'users.longitude',
            ])
            ->selectRaw("(6371 * acos(\n                cos(radians(?)) * cos(radians(users.latitude)) *\n                cos(radians(users.longitude) - radians(?)) +\n                sin(radians(?)) * sin(radians(users.latitude))\n            )) AS distance", [$lat, $lng, $lat])
            ->selectRaw('AVG(reviews.rate) as avg_rating')
            ->selectRaw('COUNT(reviews.id) as review_count')
            ->leftJoin('reviews', 'users.id', '=', 'reviews.tutor_id')
            ->with(['tutor', 'tutor.subjects'])
            ->where('users.id', $id)
            ->where('users.role', 'tutor')
            ->groupBy([
                'users.id',
                'users.name',
                'users.telephone_number',
                'users.profile_photo_path',
                'users.gender',
                'users.date_of_birth',
                'users.religion',
                'users.home_address',
                'users.latitude',
                'users.longitude',
            ])
            ->first();

        if (!$tutor) {
            return new ResponseDTO([
                'status' => 'error',
                'message' => 'Tutor tidak ditemukan',
                'errors' => [
                    'tutor_id' => $id,
                ],
            ], 404);
        }

        $address = is_string($tutor->home_address)
            ? json_decode($tutor->home_address, true)
            : $tutor->home_address;
        $address = is_array($address) ? $address : [];

        $subjects = $tutor->tutor?->subjects
            ? $tutor->tutor->subjects->map(fn($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
            ])->values()
            : collect();

        return new ResponseDTO([
            'status' => 'success',
            'message' => 'Berhasil mengambil detail tutor',
            'data' => [
                'user_id' => $tutor->id,
                'name' => $tutor->name,
                'telephone_number' => $tutor->telephone_number,
                'profile_photo_path' => $tutor->profile_photo_path,
                'gender' => $tutor->gender,
                'date_of_birth' => $tutor->date_of_birth,
                'religion' => $tutor->religion,
                'distance_km' => round($tutor->distance, 2),
                'address' => [
                    'subdistrict' => $address['subdistrict'] ?? null,
                    'district' => $address['district'] ?? null,
                    'regency' => $address['regency'] ?? null,
                    'province' => $address['province'] ?? null,
                    'full_text' => implode(', ', array_filter([
                        $address['subdistrict'] ?? null,
                        $address['district'] ?? null,
                        $address['regency'] ?? null,
                    ])),
                ],
                'rating' => [
                    'average_rating' => round($tutor->avg_rating ?? 0, 1),
                    'total_reviews' => (int) ($tutor->review_count ?? 0),
                ],
                'subjects' => $subjects,
                'tutor' => [
                    'price' => $tutor->tutor?->price,
                    'price_formatted' => $tutor->tutor?->price
                        ? 'Rp ' . number_format($tutor->tutor->price, 0, ',', '.')
                        : null,
                    'experience' => $tutor->tutor?->experience,
                    'badge' => $tutor->tutor?->badge,
                    'status' => $tutor->tutor?->status,
                    'description' => $tutor->tutor?->description,
                    'teaching_mode' => $tutor->tutor?->learning_method,
                ],
            ],
        ], 200);
    }
}
