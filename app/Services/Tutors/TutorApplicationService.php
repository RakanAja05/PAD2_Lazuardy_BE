<?php

namespace App\Services\Tutors;

use App\DTOs\ResponseDTO;
use App\Enums\FileTypeEnum;
use App\Http\Requests\StoreTutorApplicationRequest;
use App\Services\TutorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorApplicationService
{
    public function index(Request $request): ResponseDTO
    {
        $user = $request->user()->load(['tutor', 'files']);
        $tutor = $user->tutor;

        if (!$tutor) {
            return new ResponseDTO([
                'status' => 'error',
                'message' => 'Tutor tidak ditemukan',
                'errors' => [
                    'tutor' => 'not_found',
                ],
            ], 404);
        }

        $filesByType = $user->files
            ->groupBy('type')
            ->map(function ($files) {
                return $files
                    ->map(fn($file) => [
                        'id' => $file->id,
                        'name' => $file->name,
                        'path_url' => $file->path_url,
                        'type' => $file->type,
                    ])
                    ->values();
            });

        $data = [
            'experience' => $tutor->experience,
            'organization' => $tutor->organization,
        ];

        foreach (FileTypeEnum::list() as $type) {
            $data[$type] = $filesByType->get($type, collect());
        }

        return new ResponseDTO([
            'status' => 'success',
            'message' => 'Berhasil mengambil data formulir pendaftaran tutor',
            'data' => $data,
        ], 200);
    }

    public function store(StoreTutorApplicationRequest $request): ResponseDTO
    {
        $request->validated();
        $user = $request->user()->load(['tutor']);
        $tutor = $user->tutor;

        $tutorData = $request->only([
            'experience', 'organization',
        ]);

        $fileData = $request->only([
            'cv', 'id_card', 'diploma',
            'certificate', 'portfolio',
        ]);

        $tutorService = new TutorService();
        DB::beginTransaction();
        try {
            $tutor->update($tutorData);
            $tutorService->storeTutorFile($user, collect($fileData));
            DB::commit();

            return new ResponseDTO([
                'status' => 'success',
                'message' => 'Berhasil menyelesaikan formulir pendaftaran tutor',
                'data' => [],
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return new ResponseDTO([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => [
                    'detail' => $e->getMessage(),
                ],
            ], 500);
        }
    }
}
