<?php

namespace App\Services\Dashboards;

use App\DTOs\ResponseDTO;
use App\Enums\OrderStatusEnum;
use App\Enums\TakenScheduleStatusEnum;
use App\Enums\TutorStatusEnum;
use App\Models\OrderItem;
use App\Models\TakenSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class StudentDashboardService
{
    public function index(Request $request): ResponseDTO
    {
        $user = $request->user();

        if (!$user->student) {
            return new ResponseDTO(
                'error',
                'User bukan student',
                null,
                [
                    'role' => 'student_required',
                ],
                403
            );
        }

        $student = $user->student;

        $now = Carbon::now();
        $endOfNextWeek = $now->copy()->addWeek()->endOfWeek();
        $startOfNextWeek = $now->copy()->addWeek()->startOfWeek();

        $profile = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'telephone_number' => $user->telephone_number,
            'profile_photo_path' => $user->profile_photo_path,
            'date_of_birth' => $user->date_of_birth,
            'gender' => $user->gender,
            'religion' => $user->religion,
            'home_address' => $user->home_address,
            'student_id' => $student->user_id,
            'class' => $student->class ? $student->class->name : null,
            'school' => $student->school,
            'parent_name' => $student->parent,
            'parent_telephone_number' => $student->parent_telephone_number,
        ];

        // Remove nulls but keep 0/false values.
        $profile = array_filter($profile, static fn($value) => $value !== null);

        // Sessions are derived from PAID purchases (orders_items -> packages.session).
        $totalSessions = (int) (OrderItem::query()
            ->join('orders', 'orders_items.order_id', '=', 'orders.id')
            ->join('packages', 'orders_items.package_id', '=', 'packages.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->selectRaw('COALESCE(SUM(orders_items.qty * COALESCE(packages.session, 0)), 0) as total_sessions')
            ->value('total_sessions') ?? 0);

        $usedSessions = (int) TakenSchedule::query()
            ->where('student_id', $user->id)
            ->where('status', TakenScheduleStatusEnum::COMPLETED->value)
            ->count();

        $remainingSessions = max(0, $totalSessions - $usedSessions);

        $session = [
            'remaining_sessions' => $remainingSessions,
            'total_sessions' => $totalSessions,
            'used_sessions' => $usedSessions,
        ];

        $subjects = TakenSchedule::query()
            ->where('student_id', $user->id)
            ->with('subject')
            ->get()
            ->unique('subject_id')
            ->map(function ($ts) {
                $subject = $ts->subject;
                if (!$subject) {
                    return null;
                }

                return array_filter([
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'icon' => $subject->icon_image_path ?? null,
                ], static fn($value) => $value !== null);
            })
            ->filter()
            ->values();

        // Upcoming schedules: booked by student, future only, this week + next week.
        $upcomingSchedules = TakenSchedule::query()
            ->where('student_id', $user->id)
            ->where('date', '>=', $now)
            ->where('date', '<=', $endOfNextWeek)
            ->where(function ($query) {
                $query
                    ->whereNull('status')
                    ->orWhereNotIn('status', [
                        TakenScheduleStatusEnum::COMPLETED->value,
                        TakenScheduleStatusEnum::EXPIRED->value,
                        TakenScheduleStatusEnum::CANCELLED->value,
                    ]);
            })
            ->with(['scheduleTutor.user', 'subject'])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function (TakenSchedule $ts) use ($startOfNextWeek) {
                $dateTime = $ts->date instanceof Carbon ? $ts->date : Carbon::parse($ts->date);
                $startTime = $dateTime->format('H:i');
                // No duration column exists in current schema; assume 1 hour.
                $endTime = $dateTime->copy()->addHour()->format('H:i');

                $weekGroup = $dateTime->lt($startOfNextWeek) ? 'this_week' : 'next_week';

                return array_filter([
                    'id' => $ts->id,
                    'subject' => $ts->subject?->name ?? null,
                    'tutor' => $ts->scheduleTutor?->user ? array_filter([
                        'id' => $ts->scheduleTutor->tutor_id,
                        'name' => $ts->scheduleTutor->user->name,
                    ], static fn($v) => $v !== null) : null,
                    'date' => $dateTime->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $ts->status?->value ?? $ts->status,
                    'is_today' => $dateTime->isToday(),
                    'week' => $weekGroup,
                ], static fn($value) => $value !== null);
            })
            ->values();

        $next = $upcomingSchedules->first();
        $nextSchedule = $next
            ? array_filter([
                'date' => $next['date'] ?? null,
                'start_time' => $next['start_time'] ?? null,
                'subject' => $next['subject'] ?? null,
            ], static fn($value) => $value !== null)
            : null;

        $summary = array_filter([
            'upcoming_count' => $upcomingSchedules->count(),
            'sessions_left' => $remainingSessions,
            'next_schedule' => $nextSchedule,
        ], static fn($value) => $value !== null);

        return new ResponseDTO(
            'success',
            'Berhasil mengambil dashboard student',
            array_filter([
                'profile' => $profile,
                'session' => $session,
                'upcoming_schedules' => $upcomingSchedules,
                'summary' => $summary,
                'subjects' => $subjects->isNotEmpty() ? $subjects : null,
            ], static fn($value) => $value !== null),
            null,
            200
        );
    }

    public function getRecommendedTutors(Request $request): ResponseDTO
    {
        $user = $request->user();

        if (!$user->student) {
            return new ResponseDTO(
                'error',
                'User bukan student',
                null,
                [
                    'role' => 'student_required',
                ],
                403
            );
        }

        $studentAddress = $user->home_address;

        $studentCity = null;
        if (is_array($studentAddress)) {
            $studentCity = $studentAddress['regency']
                ?? $studentAddress['city']
                ?? $studentAddress['district']
                ?? $studentAddress['province']
                ?? null;
        }

        $tutorsQuery = User::query()
            ->select(['id', 'name', 'profile_photo_path', 'home_address'])
            ->where('role', 'tutor')
            ->whereHas('tutor', function ($query) {
                $query->where('status', TutorStatusEnum::VERIFIED->value);
            })
            ->with([
                'tutor:user_id,description,learning_method',
                'subjects:id,name,icon_image_path',
            ])
            ->withAvg('reviewTutors as rating_average', 'rate')
            ->withCount('reviewTutors as rating_count');

        // Filtering:
        // - Same city tutors always included
        // - Out-of-city tutors included ONLY if they support "online"
        if ($studentCity) {
            $tutorsQuery->where(function ($query) use ($studentCity) {
                $query
                    ->whereRaw("JSON_EXTRACT(home_address, '$.regency') = ?", [$studentCity])
                    ->orWhere(function ($q) use ($studentCity) {
                        $q
                            ->whereRaw("home_address IS NULL OR JSON_EXTRACT(home_address, '$.regency') != ?", [$studentCity])
                            ->whereHas('tutor', function ($tutorQuery) {
                                $tutorQuery->whereJsonContains('learning_method', 'online');
                            });
                    });
            });
        } else {
            // If student city is not available, only show tutors that can teach online.
            $tutorsQuery->whereHas('tutor', function ($query) {
                $query->whereJsonContains('learning_method', 'online');
            });
        }

        // Sorting: highest rating first (real-time AVG + COUNT from reviews table).
        $tutorsQuery
            ->orderByDesc('rating_average')
            ->orderByDesc('rating_count')
            ->orderBy('name');

        $tutors = $tutorsQuery->paginate(5);

        return new ResponseDTO(
            'success',
            'Berhasil mengambil rekomendasi tutor',
            [
                'tutors' => $tutors->map(function (User $tutor) {
                    $tutorAddress = is_array($tutor->home_address) ? $tutor->home_address : null;
                    $city = $tutorAddress
                        ? ($tutorAddress['regency'] ?? $tutorAddress['city'] ?? $tutorAddress['district'] ?? $tutorAddress['province'] ?? null)
                        : null;

                    $learningMethod = $tutor->tutor?->learning_method;
                    $teachingMode = is_array($learningMethod)
                        ? array_values(array_filter($learningMethod, static fn ($v) => is_string($v) && $v !== ''))
                        : [];

                    $description = $tutor->tutor?->description;
                    if (is_string($description)) {
                        $description = trim(preg_replace('/\s+/', ' ', $description));
                        $description = Str::limit($description, 120, '...');
                    } else {
                        $description = null;
                    }

                    $subjects = $tutor->subjects
                        ->map(function ($subject) {
                            return array_filter([
                                'id' => $subject->id,
                                'name' => $subject->name,
                                'icon' => $subject->icon_image_path,
                            ], static fn ($value) => $value !== null);
                        })
                        ->values();

                    $payload = [
                        'id' => $tutor->id,
                        'name' => $tutor->name,
                        'photo' => $tutor->profile_photo_path,
                        'subjects' => $subjects,
                        'rating' => [
                            'average' => round((float) ($tutor->rating_average ?? 0), 2),
                            'count' => (int) ($tutor->rating_count ?? 0),
                        ],
                        'short_description' => $description,
                        'teaching_mode' => $teachingMode,
                        'city' => $city,
                    ];

                    // Remove nulls (keep photo even when null).
                    $payload = array_filter($payload, static function ($value, $key) {
                        if ($key === 'photo') {
                            return true;
                        }
                        return $value !== null;
                    }, ARRAY_FILTER_USE_BOTH);

                    // Always keep these as arrays/objects for frontend usability.
                    $payload['subjects'] = $subjects;
                    $payload['rating'] = [
                        'average' => round((float) ($tutor->rating_average ?? 0), 2),
                        'count' => (int) ($tutor->rating_count ?? 0),
                    ];
                    $payload['teaching_mode'] = $teachingMode;

                    return $payload;
                }),
                'pagination' => [
                    'current_page' => $tutors->currentPage(),
                    'last_page' => $tutors->lastPage(),
                    'per_page' => $tutors->perPage(),
                    'total' => $tutors->total(),
                    'has_more' => $tutors->hasMorePages(),
                ],
            ],
            null,
            200
        );
    }

    public function summary(Request $request): ResponseDTO
    {
        $user = $request->user();

        if (!$user->student) {
            return new ResponseDTO(
                'error',
                'User bukan student',
                null,
                [
                    'role' => 'student_required',
                ],
                403
            );
        }

        $now = Carbon::now();
        $endOfNextWeek = $now->copy()->addWeek()->endOfWeek();

        $totalSessions = (int) (OrderItem::query()
            ->join('orders', 'orders_items.order_id', '=', 'orders.id')
            ->join('packages', 'orders_items.package_id', '=', 'packages.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->selectRaw('COALESCE(SUM(orders_items.qty * COALESCE(packages.session, 0)), 0) as total_sessions')
            ->value('total_sessions') ?? 0);

        $usedSessions = (int) TakenSchedule::query()
            ->where('student_id', $user->id)
            ->where('status', TakenScheduleStatusEnum::COMPLETED->value)
            ->count();

        $remainingSessions = max(0, $totalSessions - $usedSessions);

        $upcomingBaseQuery = TakenSchedule::query()
            ->where('student_id', $user->id)
            ->where('date', '>=', $now)
            ->where('date', '<=', $endOfNextWeek)
            ->where(function ($query) {
                $query
                    ->whereNull('status')
                    ->orWhereNotIn('status', [
                        TakenScheduleStatusEnum::COMPLETED->value,
                        TakenScheduleStatusEnum::EXPIRED->value,
                        TakenScheduleStatusEnum::CANCELLED->value,
                    ]);
            });

        $upcomingCount = (int) $upcomingBaseQuery->count();

        $nextTs = (clone $upcomingBaseQuery)
            ->with('subject')
            ->orderBy('date', 'asc')
            ->first();

        $nextSchedule = null;
        if ($nextTs) {
            $dateTime = $nextTs->date instanceof Carbon ? $nextTs->date : Carbon::parse($nextTs->date);
            $nextSchedule = [
                'date' => $dateTime->toDateString(),
                'start_time' => $dateTime->format('H:i'),
                'subject' => $nextTs->subject?->name ?? null,
            ];
            $nextSchedule = array_filter($nextSchedule, static fn($value) => $value !== null);
        }

        $session = [
            'remaining_sessions' => $remainingSessions,
            'total_sessions' => $totalSessions,
            'used_sessions' => $usedSessions,
        ];

        $summary = array_filter([
            'upcoming_count' => $upcomingCount,
            'sessions_left' => $remainingSessions,
            'next_schedule' => $nextSchedule,
        ], static fn($value) => $value !== null);

        return new ResponseDTO(
            'success',
            'Berhasil mengambil ringkasan student',
            array_filter([
                'session' => $session,
                'summary' => $summary,
            ], static fn($value) => $value !== null),
            null,
            200
        );
    }
}
