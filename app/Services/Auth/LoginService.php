<?php

namespace App\Services\Auth;

use App\DTOs\ResponseDTO;
use App\Enums\RoleEnum;
use App\Enums\SubjectApplicationEnum;
use App\Http\Requests\UpdateMeRequest;
use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginService
{
    public function login(Request $request): ResponseDTO
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return new ResponseDTO(
                'error',
                'Email atau password salah',
                null,
                [
                    'credentials' => 'invalid',
                ],
                401
            );
        }

        $user = $request->user();
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return new ResponseDTO(
            'success',
            'Login berhasil',
            [
                'token' => $token,
                'user' => $user,
            ],
            null,
            200
        );
    }

    public function logout(Request $request): ResponseDTO
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return new ResponseDTO(
            'success',
            'Logout berhasil',
            [],
            null,
            200
        );
    }

    public function me(Request $request): ResponseDTO
    {
        $user = $request->user();

        return new ResponseDTO(
            'success',
            'Berhasil mengambil profil',
            $this->buildMeUser($user),
            null,
            200
        );
    }

    public function updateMe(UpdateMeRequest $request): ResponseDTO
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data) {
            $userFill = array_intersect_key($data, array_flip([
                'name',
                'telephone_number',
                'gender',
                'date_of_birth',
                'religion',
                'profile_photo_path',
                'latitude',
                'longitude',
            ]));

            if (!empty($userFill)) {
                $user->fill($userFill);
            }

            $addressPatch = [];
            foreach (['province', 'regency', 'district', 'subdistrict', 'street'] as $k) {
                if (array_key_exists($k, $data)) {
                    $addressPatch[$k] = $data[$k];
                }
            }

            if (array_key_exists('address', $data)) {
                if ($data['address'] === null) {
                    $user->home_address = null;
                } else {
                    $existing = $user->home_address ?? [];
                    $nested = is_array($data['address']) ? $data['address'] : [];
                    $user->home_address = array_merge($existing, $addressPatch, $nested);
                }
            } elseif (!empty($addressPatch)) {
                $existing = $user->home_address ?? [];
                $user->home_address = array_merge($existing, $addressPatch);
            }

            $user->save();

            if ($user->role === RoleEnum::STUDENT) {
                $student = $user->student;

                if ($student) {
                    if (array_key_exists('class_name', $data)) {
                        $classId = ClassModel::query()->where('name', $data['class_name'])->value('id');
                        if ($classId !== null) {
                            $student->class_id = $classId;
                        }
                    }

                    $student->save();
                }
            }

            if ($user->role === RoleEnum::TUTOR) {
                $tutor = $user->tutor;

                if (!$tutor) {
                    $tutor = $user->tutor()->create(['user_id' => $user->id]);
                }

                if (array_key_exists('description', $data)) {
                    $tutor->description = $data['description'];
                }

                if (array_key_exists('education', $data)) {
                    $tutor->education = $data['education'];
                }

                if (array_key_exists('teaching_mode', $data)) {
                    $value = $data['teaching_mode'];

                    if ($value === null) {
                        $tutor->learning_method = null;
                    } elseif (is_string($value)) {
                        $tutor->learning_method = [$value];
                    } else {
                        $tutor->learning_method = array_values(array_unique($value));
                    }
                }

                $bankKey = null;
                if (array_key_exists('bank_code', $data)) {
                    $bankKey = 'bank_code';
                } elseif (array_key_exists('bank', $data)) {
                    $bankKey = 'bank';
                }
                if ($bankKey !== null) {
                    $tutor->bank_code = $data[$bankKey];
                }

                $accountKey = null;
                if (array_key_exists('account_number', $data)) {
                    $accountKey = 'account_number';
                } elseif (array_key_exists('rekening', $data)) {
                    $accountKey = 'rekening';
                }
                if ($accountKey !== null) {
                    $tutor->account_number = $data[$accountKey];
                }

                $tutor->save();

                if (array_key_exists('subjects', $data)) {
                    if ($data['subjects'] === null) {
                        $user->subjects()->detach();
                    } else {
                        $existing = $user->subjects()->withPivot('status')->get();
                        $existingStatusBySubjectId = [];
                        foreach ($existing as $subject) {
                            $existingStatusBySubjectId[$subject->id] = $subject->pivot->status;
                        }

                        $syncData = [];
                        foreach (array_values(array_unique($data['subjects'])) as $subjectId) {
                            $syncData[$subjectId] = [
                                'status' => $existingStatusBySubjectId[$subjectId] ?? SubjectApplicationEnum::VERIFY->value,
                            ];
                        }

                        $user->subjects()->sync($syncData);
                    }
                }
            }
        });

        $user->refresh();

        return new ResponseDTO(
            'success',
            'Berhasil memperbarui profil',
            $this->buildMeUser($user),
            null,
            200
        );
    }

    private function buildMeUser(User $user): array
    {
        $role = $user->role;

        $user->loadMissing([
            'student.class',
            'student.parentLink.user',
            'tutor',
            'subjects',
        ]);

        $home = is_array($user->home_address) ? $user->home_address : [];

        $address = [
            'street' => $home['street'] ?? $home['address'] ?? null,
            'city' => $home['city'] ?? $home['regency'] ?? null,
            'province' => $home['province'] ?? null,
            'district' => $home['district'] ?? null,
            'subdistrict' => $home['subdistrict'] ?? null,
            'latitude' => $user->latitude !== null ? (string) $user->latitude : null,
            'longitude' => $user->longitude !== null ? (string) $user->longitude : null,
        ];

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role?->value,
            'telephone_number' => $user->telephone_number,
            'profile_photo_path' => $user->profile_photo_path,
            'gender' => $user->gender?->value,
            'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
            'religion' => $user->religion?->value,
            'address' => $address,
        ];

        if ($role === RoleEnum::STUDENT) {
            $student = $user->student;
            $parentUser = $student?->parentLink?->user;

            $data['student'] = [
                'class_name' => $student?->class?->name,
                'parent' => [
                    'name' => $parentUser?->name,
                    'telephone_number' => $parentUser?->telephone_number,
                ],
            ];
        }

        if ($role === RoleEnum::TUTOR) {
            $tutor = $user->tutor;

            $education = [];
            if (is_array($tutor?->education)) {
                foreach ($tutor->education as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $education[] = [
                        'year' => isset($row['year']) && is_numeric($row['year']) ? (int) $row['year'] : null,
                        'major' => $row['major'] ?? null,
                        'institution' => $row['institution'] ?? null,
                    ];
                }
            }

            $teachingMode = [];
            if (is_array($tutor?->learning_method)) {
                $teachingMode = array_values($tutor->learning_method);
            }

            $subjects = $user->subjects
                ->map(fn ($subject) => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                ])
                ->values()
                ->all();

            $data['tutor'] = [
                'description' => $tutor?->description,
                'education' => $education,
                'teaching_mode' => $teachingMode,
                'subjects' => $subjects,
            ];
        }

        return $data;
    }
}
