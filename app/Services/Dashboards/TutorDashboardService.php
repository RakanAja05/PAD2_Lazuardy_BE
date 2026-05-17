<?php

namespace App\Services\Dashboards;

use App\DTOs\ResponseDTO;
use App\Enums\TakenScheduleStatusEnum;
use App\Models\Review;
use App\Models\ScheduleTutor;
use App\Models\TakenSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TutorDashboardService
{
    public function index(Request $request): ResponseDTO
    {
        $user = $request->user();

        if (!$user->tutor) {
            return new ResponseDTO(
                'error',
                'User bukan tutor',
                null,
                [
                    'role' => 'tutor_required',
                ],
                403
            );
        }

        $tutor = $user->tutor;
        $now = Carbon::now();

        $profile = array_filter([
            'name' => $user->name,
            'profile_photo_path' => $user->profile_photo_path,
            'status' => $tutor->status?->value ?? $tutor->status,
        ], static fn ($value) => $value !== null);

        $subjects = $user->subjects()
            ->with('class')
            ->get()
            ->map(function ($subject) {
                return array_filter([
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'icon' => $subject->icon_image_path,
                    'class' => $subject->class?->name,
                ], static fn ($value) => $value !== null);
            })
            ->values();

        $availabilitySchedules = ScheduleTutor::query()
            ->where('tutor_id', $user->id)
            ->orderBy('day')
            ->orderBy('time')
            ->get()
            ->map(function (ScheduleTutor $schedule) {
                $startTime = substr((string) $schedule->time, 0, 5);
                $endTime = Carbon::createFromFormat('H:i', $startTime)->addHour()->format('H:i');

                return array_filter([
                    'id' => $schedule->id,
                    'day' => $schedule->day,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ], static fn ($value) => $value !== null);
            })
            ->values();

        $upcomingSessions = TakenSchedule::query()
            ->where('tutor_id', $user->id)
            ->where('date', '>=', $now)
            ->where('status', TakenScheduleStatusEnum::ACTIVE->value)
            ->with(['student', 'subject.class'])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function (TakenSchedule $ts) {
                $dateTime = $ts->date instanceof Carbon ? $ts->date : Carbon::parse($ts->date);
                $startTime = $ts->time ? substr((string) $ts->time, 0, 5) : $dateTime->format('H:i');
                // No duration column exists in current schema; assume 1 hour.
                $endTime = $dateTime->copy()->addHour()->format('H:i');

                $student = array_filter([
                    'id' => $ts->student_id,
                    'name' => $ts->student?->name,
                    'photo' => $ts->student?->profile_photo_path,
                ], static fn ($value) => $value !== null);

                $subject = $ts->subject
                    ? array_filter([
                        'id' => $ts->subject->id,
                        'name' => $ts->subject->name,
                        'icon' => $ts->subject->icon_image_path,
                        'class' => $ts->subject->class?->name,
                    ], static fn ($value) => $value !== null)
                    : null;

                return array_filter([
                    'id' => $ts->id,
                    'date' => $dateTime->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'student' => $student,
                    'subject' => $subject,
                    'status' => $ts->status?->value ?? $ts->status,
                ], static fn ($value) => $value !== null);
            })
            ->values();

        $todaySessions = (int) TakenSchedule::query()
            ->where('tutor_id', $user->id)
            ->whereBetween('date', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->where('status', TakenScheduleStatusEnum::ACTIVE->value)
            ->count();

        $completedSessions = (int) TakenSchedule::query()
            ->where('tutor_id', $user->id)
            ->where('status', TakenScheduleStatusEnum::COMPLETED->value)
            ->count();

        $ratePerSession = (int) ($tutor->salary ?? 0);
        $earningsTotal = $completedSessions * $ratePerSession;

        $ratingRow = Review::query()
            ->where('tutor_id', $user->id)
            ->selectRaw('COALESCE(AVG(rate), 0) as average, COUNT(*) as count')
            ->first();

        $rating = [
            'average' => round((float) ($ratingRow?->average ?? 0), 2),
            'count' => (int) ($ratingRow?->count ?? 0),
        ];

        $reviews = Review::query()
            ->where('tutor_id', $user->id)
            ->with('student.user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Review $review) {
                return array_filter([
                    'id' => $review->id,
                    'rating' => (float) $review->rate,
                    'comment' => $review->comment,
                    'student_name' => $review->student?->user?->name,
                    'created_at' => $review->created_at,
                ], static fn ($value) => $value !== null);
            })
            ->values();

        return new ResponseDTO(
            'success',
            'Berhasil mengambil dashboard tutor',
            [
                'profile' => $profile,
                'subjects' => $subjects,
                'availability_schedules' => $availabilitySchedules,
                'upcoming_sessions' => $upcomingSessions,
                'summary' => [
                    'upcoming_count' => (int) $upcomingSessions->count(),
                    'today_sessions' => $todaySessions,
                ],
                'earnings' => [
                    'total' => $earningsTotal,
                ],
                'rating' => $rating,
                'reviews' => $reviews,
            ],
            null,
            200
        );
    }

    public function summary(Request $request): ResponseDTO
    {
        $user = $request->user();

        if (!$user->tutor) {
            return new ResponseDTO(
                'error',
                'User bukan tutor',
                null,
                [
                    'role' => 'tutor_required',
                ],
                403
            );
        }

        $tutor = $user->tutor;

        $now = Carbon::now();
        $startOfDay = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();

        $active = TakenScheduleStatusEnum::ACTIVE->value;
        $completed = TakenScheduleStatusEnum::COMPLETED->value;

        // Single aggregated query for lightweight summary.
        $stats = TakenSchedule::query()
            ->where('tutor_id', $user->id)
            ->selectRaw(
                "\n                SUM(CASE\n                    WHEN `date` BETWEEN ? AND ?\n                        AND (status IS NULL OR status = ? OR status = ?)\n                    THEN 1 ELSE 0\n                END) as today_sessions,\n                SUM(CASE\n                    WHEN `date` > ?\n                        AND (status IS NULL OR status = ?)\n                    THEN 1 ELSE 0\n                END) as upcoming_sessions,\n                SUM(CASE\n                    WHEN status = ?\n                    THEN 1 ELSE 0\n                END) as completed_sessions\n            ",
                [
                    $startOfDay,
                    $endOfDay,
                    $active,
                    $completed,
                    $endOfDay,
                    $active,
                    $completed,
                ]
            )
            ->first();

        $todaySessions = (int) ($stats?->today_sessions ?? 0);
        $upcomingSessions = (int) ($stats?->upcoming_sessions ?? 0);
        $completedSessions = (int) ($stats?->completed_sessions ?? 0);

        $ratePerSession = (int) ($tutor->salary ?? 0);
        $totalEarnings = $completedSessions * $ratePerSession;

        return new ResponseDTO(
            'success',
            'Berhasil mengambil ringkasan tutor',
            [
                'summary' => [
                    'today_sessions' => $todaySessions,
                    'upcoming_sessions' => $upcomingSessions,
                    'completed_sessions' => $completedSessions,
                    'total_earnings' => $totalEarnings,
                ],
            ],
            null,
            200
        );
    }
}
