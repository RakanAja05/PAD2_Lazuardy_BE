<?php

namespace App\Services\Students;

use App\DTOs\ResponseDTO;
use App\Enums\TakenScheduleStatusEnum;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function index(Request $request): ResponseDTO
    {
        $user = $request->user();

        $takenSchedules = $user->takenSchedules()
            ->where('status', TakenScheduleStatusEnum::COMPLETED->value)
            ->with([
                'subject:id,name',
                'scheduleTutor.user:id,name',
                'scheduleTutor.tutor:user_id,description',
                'scheduleTutor.tutor.subjects.class',
            ])
            ->orderByDesc('date')
            ->get();

        $tutorsToReview = $takenSchedules
            ->filter(fn ($takenSchedule) => $takenSchedule->scheduleTutor?->user)
            ->unique(fn ($takenSchedule) => $takenSchedule->scheduleTutor->user->id)
            ->values()
            ->map(function ($takenSchedule) {
                $tutorUser = $takenSchedule->scheduleTutor->user;
                $tutorProfile = $takenSchedule->scheduleTutor->tutor;

                $classNames = $tutorProfile
                    ? $tutorProfile->subjects
                        ->pluck('class.name')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray()
                    : [];

                return [
                    'tutor_id' => $tutorUser->id,
                    'tutor_name' => $tutorUser->name,
                    'tutor_description' => $tutorProfile?->description,
                    'tutor_classes' => $classNames,
                    'purchased_subject_name' => $takenSchedule->subject?->name,
                ];
            });

        return new ResponseDTO(
            'success',
            'Data daftar tutor berhasil terkirim',
            $tutorsToReview,
            null,
            200
        );
    }

    public function storeOrUpdate(Request $request): ResponseDTO
    {
        $allowedKeys = ['tutor_id', 'rate', 'comment'];
        $extraKeys = array_diff(array_keys($request->all()), $allowedKeys);

        if (!empty($extraKeys)) {
            throw ValidationException::withMessages([
                'unexpected_fields' => [
                    'Field tidak didukung: ' . implode(', ', $extraKeys),
                ],
            ]);
        }

        $request->validate([
            'tutor_id' => ['required', 'integer', Rule::exists('tutors', 'user_id')],
            'rate' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $student = $request->user();

        Review::updateOrCreate([
            'student_id' => $student->id,
            'tutor_id' => (int) $request->tutor_id,
        ], [
            'rate' => $request->rate,
            'comment' => $request->comment,
        ]);

        return new ResponseDTO(
            'success',
            'Review berhasil tersimpan',
            [],
            null,
            201
        );
    }

    public function update(Request $request, int $id): ResponseDTO
    {
        $allowedKeys = ['rate', 'comment'];
        $extraKeys = array_diff(array_keys($request->all()), $allowedKeys);

        if (!empty($extraKeys)) {
            return new ResponseDTO(
                'error',
                'Validasi gagal',
                null,
                [
                    'unexpected_fields' => [
                        'Field tidak didukung: ' . implode(', ', $extraKeys),
                    ],
                ],
                422
            );
        }

        $validator = Validator::make($request->all(), [
            'rate' => ['sometimes', 'numeric', 'min:1', 'max:5'],
            'comment' => ['sometimes', 'string'],
        ]);

        if ($validator->fails()) {
            return new ResponseDTO(
                'error',
                'Validasi gagal',
                null,
                $validator->errors(),
                422
            );
        }

        $validated = $validator->validated();
        if (empty($validated)) {
            return new ResponseDTO(
                'error',
                'Validasi gagal',
                null,
                [
                    'detail' => ['Minimal kirim salah satu field: rate atau comment'],
                ],
                422
            );
        }

        $student = $request->user();

        $review = Review::find($id);
        if (!$review) {
            return new ResponseDTO(
                'error',
                'Review tidak ditemukan',
                null,
                [
                    'id' => $id,
                ],
                404
            );
        }

        if ((int) $review->student_id !== (int) $student->id) {
            return new ResponseDTO(
                'error',
                'Tidak memiliki akses untuk mengubah review ini',
                null,
                [
                    'authorization' => 'forbidden',
                ],
                403
            );
        }

        $review->fill($validated);
        $review->save();

        return new ResponseDTO(
            'success',
            'Review berhasil diupdate',
            $review->fresh(),
            null,
            200
        );
    }

    public function show(Request $request, int $tutorId): ResponseDTO
    {
        $validator = Validator::make([
            'tutor_id' => $tutorId,
        ], [
            'tutor_id' => ['required', 'integer', Rule::exists('tutors', 'user_id')],
        ]);

        if ($validator->fails()) {
            return new ResponseDTO(
                'error',
                'Validasi gagal',
                null,
                $validator->errors(),
                422
            );
        }

        $student = $request->user();

        $reviewData = Review::where('student_id', $student->id)
            ->where('tutor_id', $tutorId)
            ->first();

        return new ResponseDTO(
            'success',
            'Berhasil mengambil detail review',
            $reviewData,
            null,
            200
        );
    }
}
