<?php

namespace App\Services\Schedules;

use App\DTOs\ResponseDTO;
use Illuminate\Http\Request;

class ScheduleService
{
    public function indexStudent(Request $request): ResponseDTO
    {
        $user = $request->user()->load([
            'takenSchedules.scheduleTutor.user',
        ]);
        $takenSchedules = $user->takenSchedules;

        $tsData = [];
        foreach ($takenSchedules as $takenSchedule) {
            $schedule = $takenSchedule->scheduleTutor;

            $tsData[] = [
                'tutor_user_id' => $schedule?->tutor_id,
                'student_user_id' => $takenSchedule->student_id,
                'tutor_name' => $schedule?->user?->name,
                'day' => $schedule?->day,
                'time' => $schedule?->time,
                'status' => $takenSchedule->status?->value ?? $takenSchedule->status,
            ];
        }

        return new ResponseDTO(
            'success',
            'Data jadwal berhasil terkirim',
            [
                'schedules' => $tsData,
            ],
            null,
            200
        );
    }

    public function indexTutor(Request $request): ResponseDTO
    {
        $user = $request->user()->load('schedules.takenSchedules.student');

        $data = $user->schedules->flatMap(function ($schedule) {
            return $schedule->takenSchedules->map(function ($ts) use ($schedule) {
                return [
                    'tutor_user_id' => $schedule->tutor_id,
                    'student_user_id' => $ts->student_id,
                    'student_name' => $ts->student?->name,
                    'day' => $schedule->day,
                    'time' => $schedule->time,
                    'status' => $ts->status?->value ?? $ts->status,
                ];
            });
        });

        return new ResponseDTO(
            'success',
            'Data jadwal berhasil terkirim',
            [
                'schedules' => $data,
            ],
            null,
            200
        );
    }
}
