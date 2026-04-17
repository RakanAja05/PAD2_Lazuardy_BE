<?php

namespace App\Services\Auth;

use App\DTOs\ResponseDTO;
use App\Enums\OtpIdentifierEnum;
use App\Enums\OtpTypeEnum;
use App\Enums\ReligionEnum;
use App\Enums\RoleEnum;
use App\Http\Requests\StoreStudentRegisterRequest;
use App\Http\Requests\StoreTutorRegisterRequest;
use App\Http\Requests\UpdateAuthRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\User;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthControllerService
{
    public function sendRegisterOtp(Request $request): ResponseDTO
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $otpService = new OtpService();
        $otp = $otpService->createOtp($data['email'], OtpIdentifierEnum::EMAIL->value, OtpTypeEnum::REGISTER->value);

        return new ResponseDTO([
            'status' => 'success',
            'message' => 'OTP berhasil terkirim ke email',
            'data' => [
                'otp' => $otp['code'],
            ],
        ], 201);
    }

    public function verifyRegisterOtp(Request $request): ResponseDTO
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'otp' => ['required', 'string'],
        ]);

        $otpService = new OtpService();
        $result = $otpService->checkOtp(
            $request['otp'],
            $request['email'],
            OtpIdentifierEnum::EMAIL->value,
            OtpTypeEnum::REGISTER->value
        );

        $payload = [
            'status' => $result['status'],
            'message' => $result['message'],
        ];

        if ($result['status'] === 'success') {
            $payload['data'] = [
                'identifier' => $result['identifier'] ?? null,
            ];
        } else {
            $payload['errors'] = [
                'identifier' => $result['identifier'] ?? null,
                'code' => $result['code'] ?? null,
            ];
        }

        return new ResponseDTO($payload, $result['code']);
    }

    public function resendRegisterOtp(Request $request): ResponseDTO
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        ]);

        $otpService = new OtpService();
        $result = $otpService->resendOtp(
            $request['email'],
            OtpIdentifierEnum::EMAIL->value,
            OtpTypeEnum::REGISTER->value
        );

        $payload = [
            'status' => $result['status'],
            'message' => $result['message'],
        ];

        if ($result['status'] === 'success') {
            $payload['data'] = [
                'otp' => $result['otp_code'] ?? null,
            ];
        } else {
            $payload['errors'] = [
                'detail' => $result['message'],
            ];
        }

        return new ResponseDTO($payload, $result['code']);
    }

    public function storeStudentRegister(StoreStudentRegisterRequest $request): ResponseDTO
    {
        $request->validated();

        $userService = new UserService();
        $authService = new AuthService();

        $userData = $request->only(
            'email', 'password', 'name',
            'gender', 'date_of_birth',
            'telephone_number', 'religion',
            'latitude', 'longitude'
        );

        $socialCacheKey = null;
        $socialTempToken = $request->input('social_temp_token');
        if ($socialTempToken) {
            $socialCacheKey = 'social:register:' . $socialTempToken;
            $socialData = Cache::get($socialCacheKey);

            if (!$socialData || empty($socialData['provider']) || empty($socialData['provider_id'])) {
                return new ResponseDTO([
                    'status' => 'error',
                    'message' => 'Sesi registrasi social tidak ditemukan atau sudah kadaluarsa',
                    'errors' => [
                        'social_temp_token' => $socialTempToken,
                    ],
                ], 422);
            }

            $allowedProviders = ['google', 'facebook'];
            $provider = (string) ($socialData['provider'] ?? '');
            if (in_array($provider, $allowedProviders, true)) {
                $providerIdColumn = $provider . '_id';
                $userData[$providerIdColumn] = (string) $socialData['provider_id'];

                $emailLocked = (bool) ($socialData['email_locked'] ?? false);
                $cachedEmail = $socialData['email'] ?? null;

                if ($emailLocked && $cachedEmail !== null && $userData['email'] !== $cachedEmail) {
                    return new ResponseDTO([
                        'status' => 'error',
                        'message' => 'Email dari social login tidak boleh diubah',
                        'errors' => [
                            'email' => 'email_locked',
                        ],
                    ], 422);
                }

                if ($emailLocked && $cachedEmail !== null) {
                    $userData['email'] = $cachedEmail;
                }
            }
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $path = $file->store('uploads', 'public');
            $userData['profile_photo_path'] = $path;
        }

        $userData['password'] = Hash::make($userData['password']);
        $userData['role'] = RoleEnum::STUDENT;
        if (empty($userData['religion'])) {
            $userData['religion'] = ReligionEnum::NOT_SET;
        }
        $userData['home_address'] = $userService->convertAddressToArray(
            $request->only(['province', 'regency', 'district', 'subdistrict', 'street'])
        );

        $studentData = [
            'class_id' => $request->integer('class_id'),
            // `students.session` is required by the current migration; initialize at 0.
            'session' => 0,
        ];

        DB::beginTransaction();
        try {
            $userResult = $authService->registerUser($userData);
            $studentData['user_id'] = $userResult['user']->id;
            Student::create($studentData);

            DB::commit();

            if (!empty($socialCacheKey)) {
                Cache::forget($socialCacheKey);
            }

            return new ResponseDTO([
                'status' => 'success',
                'message' => 'Registrasi akun berhasil',
                'data' => [
                    'token' => $userResult['token'],
                ],
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return new ResponseDTO([
                'status' => 'error',
                'message' => 'Registrasi gagal: ' . $e->getMessage(),
                'errors' => [
                    'code' => $e->getCode(),
                ],
            ], 500);
        }
    }

    public function storeTutorRegister(StoreTutorRegisterRequest $request): ResponseDTO
    {
        $request->validated();

        $userService = new UserService();
        $authService = new AuthService();

        $userData = $request->only(
            'email', 'password', 'name',
            'gender', 'date_of_birth',
            'telephone_number', 'religion',
            'latitude', 'longitude'
        );

        $socialCacheKey = null;
        $socialTempToken = $request->input('social_temp_token');
        if ($socialTempToken) {
            $socialCacheKey = 'social:register:' . $socialTempToken;
            $socialData = Cache::get($socialCacheKey);

            if (!$socialData || empty($socialData['provider']) || empty($socialData['provider_id'])) {
                return new ResponseDTO([
                    'status' => 'error',
                    'message' => 'Sesi registrasi social tidak ditemukan atau sudah kadaluarsa',
                    'errors' => [
                        'social_temp_token' => $socialTempToken,
                    ],
                ], 422);
            }

            $allowedProviders = ['google', 'facebook'];
            $provider = (string) ($socialData['provider'] ?? '');
            if (in_array($provider, $allowedProviders, true)) {
                $providerIdColumn = $provider . '_id';
                $userData[$providerIdColumn] = (string) $socialData['provider_id'];

                $emailLocked = (bool) ($socialData['email_locked'] ?? false);
                $cachedEmail = $socialData['email'] ?? null;

                if ($emailLocked && $cachedEmail !== null && $userData['email'] !== $cachedEmail) {
                    return new ResponseDTO([
                        'status' => 'error',
                        'message' => 'Email dari social login tidak boleh diubah',
                        'errors' => [
                            'email' => 'email_locked',
                        ],
                    ], 422);
                }

                if ($emailLocked && $cachedEmail !== null) {
                    $userData['email'] = $cachedEmail;
                }
            }
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $path = $file->store('uploads', 'public');
            $userData['profile_photo_path'] = $path;
        }

        $userData['password'] = Hash::make($userData['password']);
        $userData['role'] = RoleEnum::TUTOR;
        if (empty($userData['religion'])) {
            $userData['religion'] = ReligionEnum::NOT_SET;
        }
        $userData['home_address'] = $userService->convertAddressToArray(
            $request->only(['province', 'regency', 'district', 'subdistrict', 'street'])
        );

        $tutorData = $request->only(['bank_code', 'account_number']);

        DB::beginTransaction();
        try {
            $userResult = $authService->registerUser($userData);
            $tutorData['user_id'] = $userResult['user']->id;
            Tutor::create($tutorData);

            DB::commit();

            if (!empty($socialCacheKey)) {
                Cache::forget($socialCacheKey);
            }

            return new ResponseDTO([
                'status' => 'success',
                'message' => 'Registrasi akun berhasil',
                'data' => [
                    'token' => $userResult['token'],
                ],
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return new ResponseDTO([
                'status' => 'error',
                'message' => 'Registrasi gagal: ' . $e->getMessage(),
                'errors' => [
                    'code' => $e->getCode(),
                ],
            ], 500);
        }
    }

    public function forgotPassword(Request $request): ResponseDTO
    {
        $validatedData = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $otpService = new OtpService();
        $user = User::getUserByEmail($validatedData['email']);

        if (!$user->exists()) {
            return new ResponseDTO([
                'status' => 'error',
                'message' => 'Email tidak ditemukan.',
                'errors' => [
                    'email' => $validatedData['email'],
                ],
            ], 404);
        }

        DB::beginTransaction();
        try {
            $otp = $otpService->createOtp(
                $user->email,
                OtpIdentifierEnum::EMAIL->value,
                OtpTypeEnum::FORGOT_PASSWORD->value,
                10,
                $user->id
            );
            $data = [
                'identifier' => $user->email,
                'identifier_type' => OtpIdentifierEnum::EMAIL->value,
                'verification_type' => OtpTypeEnum::FORGOT_PASSWORD->value,
            ];
            $caching = $otpService->storeOtpToCache(OtpTypeEnum::FORGOT_PASSWORD->value, $data);
            DB::commit();

            return new ResponseDTO([
                'status' => 'success',
                'message' => 'OTP untuk reset password telah dikirim ke email Anda.',
                'data' => [
                    'otp' => $otp['code'],
                    'temp_token' => $caching['token'],
                ],
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function verifyForgotPassword(VerifyOtpRequest $request): ResponseDTO
    {
        $request->validated();
        $otpService = new OtpService();
        $verify = $otpService->verifyOtp(
            $request->otp_code,
            OtpTypeEnum::FORGOT_PASSWORD->value,
            $request->temp_token
        );

        $tokenReset = Str::random(15);
        Cache::put('auth:reset-password:' . $tokenReset, ['email' => $verify['identifier']], 1800);

        $payload = [
            'status' => $verify['status'],
            'message' => $verify['message'],
        ];

        if ($verify['status'] === 'success') {
            $payload['data'] = [
                'token' => $tokenReset,
            ];
        } else {
            $payload['errors'] = [
                'identifier' => $verify['identifier'] ?? null,
                'code' => $verify['code'] ?? null,
            ];
        }

        return new ResponseDTO($payload, $verify['code']);
    }

    public function resetPassword(UpdateAuthRequest $request): ResponseDTO
    {
        $validatedData = $request->validated();

        $cacheKey = 'auth:reset-password:' . $validatedData['token'];
        $cacheData = Cache::get($cacheKey);
        $user = User::getUserByEmail($cacheData['email']);

        if (!$user->exists()) {
            return new ResponseDTO([
                'status' => 'error',
                'message' => 'Email tidak ditemukan.',
                'errors' => [
                    'email' => $cacheData['email'] ?? null,
                ],
            ], 404);
        }

        DB::beginTransaction();
        try {
            $user->password = Hash::make($validatedData['password']);
            $user->save();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return new ResponseDTO([
            'status' => 'success',
            'message' => 'Password berhasil direset.',
            'data' => [],
        ], 200);
    }

}
