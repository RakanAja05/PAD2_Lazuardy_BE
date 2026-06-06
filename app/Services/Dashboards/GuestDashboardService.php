<?php

namespace App\Services\Dashboards;

use App\DTOs\ResponseDTO;
use App\Enums\RoleEnum;
use App\Enums\TutorStatusEnum;
use App\Models\Review;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class GuestDashboardService
{
    public function index(Request $request): ResponseDTO
    {
        $verifiedTutorsCount = (int) User::query()
            ->where('role', RoleEnum::TUTOR->value)
            ->whereHas('tutor', function ($query) {
                $query->where('status', TutorStatusEnum::VERIFIED->value);
            })
            ->count();

        $studentsCount = (int) User::query()
            ->where('role', RoleEnum::STUDENT->value)
            ->count();

        $subjectsCount = (int) Subject::query()->count();
        $reviewsCount = (int) Review::query()->count();

        $featuredTutors = User::query()
            ->select(['id', 'name', 'profile_photo_path', 'home_address'])
            ->where('role', RoleEnum::TUTOR->value)
            ->whereHas('tutor', function ($query) {
                $query->where('status', TutorStatusEnum::VERIFIED->value);
            })
            ->with([
                'tutor:user_id,description,learning_method',
                'subjects:id,name,icon_image_path',
            ])
            ->withAvg('reviewTutors as rating_average', 'rate')
            ->withCount('reviewTutors as rating_count')
            ->orderByDesc('rating_average')
            ->orderByDesc('rating_count')
            ->orderBy('name')
            ->limit(6)
            ->get()
            ->map(function (User $tutor) {
                $address = is_array($tutor->home_address) ? $tutor->home_address : [];

                return [
                    'id' => $tutor->id,
                    'name' => $tutor->name,
                    'photo' => $tutor->profile_photo_path,
                    'city' => $address['regency'] ?? $address['city'] ?? $address['district'] ?? null,
                    'teaching_mode' => is_array($tutor->tutor?->learning_method)
                        ? array_values($tutor->tutor->learning_method)
                        : [],
                    'short_description' => $tutor->tutor?->description,
                    'subjects' => $tutor->subjects
                        ->map(function ($subject) {
                            return [
                                'id' => $subject->id,
                                'name' => $subject->name,
                                'icon' => $subject->icon_image_path,
                            ];
                        })
                        ->values(),
                    'rating' => [
                        'average' => round((float) ($tutor->rating_average ?? 0), 2),
                        'count' => (int) ($tutor->rating_count ?? 0),
                    ],
                ];
            })
            ->values();

        return new ResponseDTO(
            'success',
            'Berhasil mengambil dashboard guest',
            [
                'summary' => [
                    'verified_tutors' => $verifiedTutorsCount,
                    'students' => $studentsCount,
                    'subjects' => $subjectsCount,
                    'reviews' => $reviewsCount,
                ],
                'featured_tutors' => $featuredTutors,
            ],
            null,
            200
        );
    }
}
