<?php

namespace App\Services\Tutors;

use App\DTOs\ResponseDTO;
use App\Models\Presence;
use Illuminate\Http\Request;
use App\Models\TakenSchedule;
use App\Enums\TakenScheduleStatusEnum;
use App\Models\Tutor;

class PresenceService
{
    public function index(Request $request): ResponseDTO
    {
        $user = $request->user()->load([
            'takenSchedules.student.student.class',
            'takenSchedules.subject',
            'takenSchedules.tutor',
        ]);

        $data = $user->takenSchedules
            ->map(function ($takenSchedule) {
                $student = $takenSchedule->student;

                return [
                    'taken_schedule_id' => $takenSchedule->id,
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'student_telephone_number' => $student->telephone_number,
                    'student_latitude' => $student->latitude,
                    'student_longitude' => $student->longitude,
                    'student_class' => $student->student->class->name,
                    'subject_id' => $takenSchedule->subject->id,
                    'subject_name' => $takenSchedule->subject->name,
                    'date' => $takenSchedule->date,
                    'status' => $takenSchedule->status,
                ];
            });

        return new ResponseDTO(
            'success',
            'Berhasil mengambil data presensi',
            $data,
            null,
            200
        );
    }

    public function store(Request $request): ResponseDTO
    {
        $request->validate([
            'taken_schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'student_id'        => ['required', 'integer', 'exists:users,id'],
            'topic'             => ['required', 'string'],
            'note'              => ['nullable', 'string'],
            'photo'             => ['required', 'file', 'mimes:png,jpg,pdf,svg,webp'],
        ]);

        $user = $request->user();

        $takenSchedule = TakenSchedule::query()
            ->where('id', $request->taken_schedule_id)
            ->where('tutor_id', $user->id)
            ->where('status', TakenScheduleStatusEnum::ACTIVE->value)
            ->first();

        if (!$takenSchedule) {
            return new ResponseDTO('error', 'Jadwal tidak ditemukan atau tidak aktif', null, ['taken_schedule_id' => 'invalid'], 404);
        }

        if (!$request->hasFile('photo')) {
            return new ResponseDTO('error', 'Presensi gagal', null, ['photo' => 'file_missing'], 422);
        }

        $path = $request->file('photo')->store('uploads', 'public');

        $presenceData = $request->only(['topic', 'note']);
        $presenceData['schedule_id'] = $request->taken_schedule_id;
        $presenceData['student_id']  = $request->student_id;
        $presenceData['tutor_id']    = $user->id;
        $presenceData['pbm_image_url'] = $path;

        Presence::create($presenceData);

        $takenSchedule->update(['status' => TakenScheduleStatusEnum::COMPLETED->value]);

        Tutor::query()
            ->where('user_id', $user->id)
            ->increment('salary');

        return new ResponseDTO('success', 'Presensi berhasil', [], null, 201);
    }
}
