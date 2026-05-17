<?php

namespace App\Services\Parents;

use App\DTOs\ResponseDTO;
use App\Models\ParentModel;
use App\Models\Presence;
use App\Models\TakenSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class ParentService
{
    public function schedules(Request $request): ResponseDTO
    {
        $studentUser = $this->resolveStudentUser($request);
        if (!$studentUser) {
            return new ResponseDTO(
                'error',
                'Data siswa tidak ditemukan untuk akun orang tua ini',
                null,
                ['student' => 'missing'],
                404
            );
        }

        $takenSchedules = TakenSchedule::query()
            ->where('student_id', $studentUser->id)
            ->with(['tutor', 'subject'])
            ->orderByDesc('date')
            ->get();

        $data = $takenSchedules->map(function (TakenSchedule $schedule) {
            $date = $schedule->date?->toDateString();

            return [
                'id' => $schedule->id,
                'tutor_user_id' => $schedule->tutor_id,
                'student_user_id' => $schedule->student_id,
                'tutor_name' => $schedule->tutor?->name,
                'subject_id' => $schedule->subject_id,
                'subject_name' => $schedule->subject?->name,
                'date' => $date,
                'day' => $schedule->date?->dayOfWeekIso,
                'time' => $schedule->time,
                'status' => $schedule->status?->value ?? $schedule->status,
            ];
        })->values();

        return new ResponseDTO(
            'success',
            'Data jadwal anak berhasil terkirim',
            [
                'student' => [
                    'id' => $studentUser->id,
                    'name' => $studentUser->name,
                ],
                'schedules' => $data,
            ],
            null,
            200
        );
    }

    public function learningResults(Request $request): ResponseDTO
    {
        $studentUser = $this->resolveStudentUser($request);
        if (!$studentUser) {
            return new ResponseDTO(
                'error',
                'Data siswa tidak ditemukan untuk akun orang tua ini',
                null,
                ['student' => 'missing'],
                404
            );
        }

        $presences = Presence::query()
            ->where('student_user_id', $studentUser->id)
            ->orderByDesc('created_at')
            ->get();

        $scheduleIds = $presences->pluck('taken_schedule_id')
            ->filter()
            ->unique()
            ->values();

        $schedules = TakenSchedule::query()
            ->whereIn('id', $scheduleIds)
            ->with(['tutor', 'subject'])
            ->get()
            ->keyBy('id');

        $data = $presences->map(function (Presence $presence) use ($schedules) {
            $schedule = $schedules->get($presence->taken_schedule_id);

            return [
                'presence_id' => $presence->id,
                'taken_schedule_id' => $presence->taken_schedule_id,
                'tutor_user_id' => $presence->tutor_user_id,
                'student_user_id' => $presence->student_user_id,
                'tutor_name' => $schedule?->tutor?->name,
                'subject_id' => $schedule?->subject_id,
                'subject_name' => $schedule?->subject?->name,
                'date' => $schedule?->date?->toDateString(),
                'time' => $schedule?->time,
                'evaluation' => $presence->evaluation,
                'report' => $presence->report,
                'pbm_image_url' => $presence->pbm_image_url,
                'created_at' => $presence->created_at?->toDateTimeString(),
            ];
        })->values();

        return new ResponseDTO(
            'success',
            'Data hasil pembelajaran berhasil terkirim',
            [
                'student' => [
                    'id' => $studentUser->id,
                    'name' => $studentUser->name,
                ],
                'results' => $data,
            ],
            null,
            200
        );
    }

    private function resolveStudentUser(Request $request): ?User
    {
        $parent = ParentModel::query()
            ->with('student.user')
            ->where('user_id', $request->user()->id)
            ->first();

        return $parent?->student?->user;
    }
}
