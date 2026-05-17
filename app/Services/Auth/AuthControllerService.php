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
use App\Models\ParentModel;
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
    private const PARENT_REGISTER_CACHE_PREFIX = 'registration:parent:';

    public function sendRegisterOtp(Request $request): ResponseDTO
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $otpService = new OtpService();
        $otp = $otpService->createOtp($data['email'], OtpIdentifierEnum::EMAIL->value, OtpTypeEnum::REGISTER->value);

        return new ResponseDTO(
            'success',
            'OTP berhasil terkirim ke email',
            [
                'otp' => $otp['code'],
            ],
            null,
            201
        );
    }

    public function verifyRegisterOtp(Request $request): ResponseDTO
    {
        if ($request->filled('temp_token')) {
            $data = $request->validate([
                'temp_token' => ['required', 'string'],
                'otp' => ['required', 'string'],
            ]);

            $cacheKey = self::PARENT_REGISTER_CACHE_PREFIX . $data['temp_token'];
            $cacheData = Cache::get($cacheKey);
            if (!$cacheData || empty($cacheData['email'])) {
                return new ResponseDTO(
                    'error',
                    'Sesi registrasi orang tua tidak ditemukan atau sudah kadaluarsa',
                    null,
                    [
                        'temp_token' => 'invalid',
                    ],
                    422
                );
            }

            $otpService = new OtpService();
            $result = $otpService->checkOtp(
                $data['otp'],
                $cacheData['email'],
                OtpIdentifierEnum::EMAIL->value,
                OtpTypeEnum::REGISTER->value
            );

            if (($result['status'] ?? null) === 'success') {
                $cacheData['parent_verified'] = true;
                Cache::put($cacheKey, $cacheData, 1800);

                return new ResponseDTO(
                    'success',
                    (string) ($result['message'] ?? ''),
                    [
                        'temp_token' => $data['temp_token'],
                    ],
                    null,
                    (int) ($result['code'] ?? 200)
                );
            }

            return new ResponseDTO(
                'error',
                (string) ($result['message'] ?? ''),
                null,
                [
                    'code' => $result['code'] ?? null,
                ],
                (int) ($result['code'] ?? 400)
            );
        }

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

        if (($result['status'] ?? null) === 'success') {
            return new ResponseDTO(
                'success',
                (string) ($result['message'] ?? ''),
                [
                    'identifier' => $result['identifier'] ?? null,
                ],
                null,
                (int) ($result['code'] ?? 200)
            );
        }

        return new ResponseDTO(
            'error',
            (string) ($result['message'] ?? ''),
            null,
            [
                'identifier' => $result['identifier'] ?? null,
                'code' => $result['code'] ?? null,
            ],
            (int) ($result['code'] ?? 400)
        );
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

        if (($result['status'] ?? null) === 'success') {
            return new ResponseDTO(
                'success',
                (string) ($result['message'] ?? ''),
                [
                    'otp' => $result['otp_code'] ?? null,
                ],
                null,
                (int) ($result['code'] ?? 200)
            );
        }

        return new ResponseDTO(
            'error',
            (string) ($result['message'] ?? ''),
            null,
            [
                'detail' => $result['message'] ?? null,
            ],
            (int) ($result['code'] ?? 400)
        );
    }

    public function sendParentRegisterOtp(Request $request): ResponseDTO
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $otpService = new OtpService();
        $otp = $otpService->createOtp(
            $data['email'],
            OtpIdentifierEnum::EMAIL->value,
            OtpTypeEnum::REGISTER->value
        );

        $tempToken = Str::random(15);
        Cache::put(self::PARENT_REGISTER_CACHE_PREFIX . $tempToken, [
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'parent_verified' => false,
        ], 1800);

        return new ResponseDTO(
            'success',
            'OTP berhasil terkirim ke email',
            [
                'otp' => $otp['code'],
                'temp_token' => $tempToken,
            ],
            null,
            201
        );
    }

    public function sendParentChildOtp(Request $request): ResponseDTO
    {
        $data = $request->validate([
            'temp_token' => ['required', 'string'],
            'child_email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
        ]);

        $cacheKey = self::PARENT_REGISTER_CACHE_PREFIX . $data['temp_token'];
        $cacheData = Cache::get($cacheKey);
        if (!$cacheData || empty($cacheData['email']) || empty($cacheData['parent_verified'])) {
            return new ResponseDTO(
                'error',
                'Sesi registrasi orang tua tidak valid atau belum terverifikasi',
                null,
                [
                    'temp_token' => 'invalid',
                ],
                422
            );
        }

        if ($cacheData['email'] === $data['child_email']) {
            return new ResponseDTO(
                'error',
                'Email anak tidak boleh sama dengan email orang tua',
                null,
                [
                    'child_email' => 'same_as_parent',
                ],
                422
            );
        }

        $studentUser = User::where('email', $data['child_email'])->first();
        if (!$studentUser || $studentUser->role !== RoleEnum::STUDENT) {
            return new ResponseDTO(
                'error',
                'Email anak tidak terdaftar sebagai siswa',
                null,
                [
                    'child_email' => 'not_student',
                ],
                422
            );
        }

        $student = Student::where('user_id', $studentUser->id)->first();
        if (!$student) {
            return new ResponseDTO(
                'error',
                'Data siswa tidak ditemukan',
                null,
                [
                    'child_email' => 'student_missing',
                ],
                404
            );
        }

        if (ParentModel::where('student_id', $studentUser->id)->exists()) {
            return new ResponseDTO(
                'error',
                'Siswa sudah terhubung dengan orang tua',
                null,
                [
                    'child_email' => 'already_linked',
                ],
                409
            );
        }

        $otpService = new OtpService();
        $otp = $otpService->createOtp(
            $data['child_email'],
            OtpIdentifierEnum::EMAIL->value,
            OtpTypeEnum::REGISTER->value
        );

        $cacheData['child_email'] = $data['child_email'];
        Cache::put($cacheKey, $cacheData, 1800);

        return new ResponseDTO(
            'success',
            'OTP berhasil terkirim ke email anak',
            [
                'otp' => $otp['code'],
            ],
            null,
            200
        );
    }

    public function verifyParentChildOtp(Request $request): ResponseDTO
    {
        $data = $request->validate([
            'temp_token' => ['required', 'string'],
            'child_email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
            'otp' => ['required', 'string'],
        ]);

        $cacheKey = self::PARENT_REGISTER_CACHE_PREFIX . $data['temp_token'];
        $cacheData = Cache::get($cacheKey);
        if (!$cacheData || empty($cacheData['email']) || empty($cacheData['parent_verified'])) {
            return new ResponseDTO(
                'error',
                'Sesi registrasi orang tua tidak valid atau belum terverifikasi',
                null,
                [
                    'temp_token' => 'invalid',
                ],
                422
            );
        }

        if (($cacheData['child_email'] ?? null) !== $data['child_email']) {
            return new ResponseDTO(
                'error',
                'Email anak tidak sesuai dengan sesi registrasi',
                null,
                [
                    'child_email' => 'mismatch',
                ],
                422
            );
        }

        $otpService = new OtpService();
        $result = $otpService->checkOtp(
            $data['otp'],
            $data['child_email'],
            OtpIdentifierEnum::EMAIL->value,
            OtpTypeEnum::REGISTER->value
        );

        if (($result['status'] ?? null) !== 'success') {
            return new ResponseDTO(
                'error',
                (string) ($result['message'] ?? ''),
                null,
                [
                    'code' => $result['code'] ?? null,
                ],
                (int) ($result['code'] ?? 400)
            );
        }

        $studentUser = User::where('email', $data['child_email'])->first();
        if (!$studentUser || $studentUser->role !== RoleEnum::STUDENT) {
            return new ResponseDTO(
                'error',
                'Email anak tidak terdaftar sebagai siswa',
                null,
                [
                    'child_email' => 'not_student',
                ],
                422
            );
        }

        if (ParentModel::where('student_id', $studentUser->id)->exists()) {
            return new ResponseDTO(
                'error',
                'Siswa sudah terhubung dengan orang tua',
                null,
                [
                    'child_email' => 'already_linked',
                ],
                409
            );
        }

        $authService = new AuthService();

        DB::beginTransaction();
        try {
            $userResult = $authService->registerUser([
                'email' => $cacheData['email'],
                'password' => $cacheData['password'],
                'role' => RoleEnum::PARENT,
            ]);

            ParentModel::create([
                'user_id' => $userResult['user']->id,
                'student_id' => $studentUser->id,
            ]);

            DB::commit();

            Cache::forget($cacheKey);

            return new ResponseDTO(
                'success',
                'Registrasi orang tua berhasil',
                [
                    'token' => $userResult['token'],
                ],
                null,
                201
            );
        } catch (Exception $e) {
            DB::rollBack();

            return new ResponseDTO(
                'error',
                'Registrasi gagal: ' . $e->getMessage(),
                null,
                [
                    'code' => $e->getCode(),
                ],
                500
            );
        }
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
                return new ResponseDTO(
                    'error',
                    'Sesi registrasi social tidak ditemukan atau sudah kadaluarsa',
                    null,
                    [
                        'social_temp_token' => $socialTempToken,
                    ],
                    422
                );
            }

            $allowedProviders = ['google', 'facebook'];
            $provider = (string) ($socialData['provider'] ?? '');
            if (in_array($provider, $allowedProviders, true)) {
                $providerIdColumn = $provider . '_id';
                $userData[$providerIdColumn] = (string) $socialData['provider_id'];

                $emailLocked = (bool) ($socialData['email_locked'] ?? false);
                $cachedEmail = $socialData['email'] ?? null;

                if ($emailLocked && $cachedEmail !== null && $userData['email'] !== $cachedEmail) {
                    return new ResponseDTO(
                        'error',
                        'Email dari social login tidak boleh diubah',
                        null,
                        [
                            'email' => 'email_locked',
                        ],
                        422
                    );
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

            return new ResponseDTO(
                'success',
                'Registrasi akun berhasil',
                [
                    'token' => $userResult['token'],
                ],
                null,
                201
            );
        } catch (Exception $e) {
            DB::rollBack();

            return new ResponseDTO(
                'error',
                'Registrasi gagal: ' . $e->getMessage(),
                null,
                [
                    'code' => $e->getCode(),
                ],
                500
            );
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
                return new ResponseDTO(
                    'error',
                    'Sesi registrasi social tidak ditemukan atau sudah kadaluarsa',
                    null,
                    [
                        'social_temp_token' => $socialTempToken,
                    ],
                    422
                );
            }

            $allowedProviders = ['google', 'facebook'];
            $provider = (string) ($socialData['provider'] ?? '');
            if (in_array($provider, $allowedProviders, true)) {
                $providerIdColumn = $provider . '_id';
                $userData[$providerIdColumn] = (string) $socialData['provider_id'];

                $emailLocked = (bool) ($socialData['email_locked'] ?? false);
                $cachedEmail = $socialData['email'] ?? null;

                if ($emailLocked && $cachedEmail !== null && $userData['email'] !== $cachedEmail) {
                    return new ResponseDTO(
                        'error',
                        'Email dari social login tidak boleh diubah',
                        null,
                        [
                            'email' => 'email_locked',
                        ],
                        422
                    );
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

            return new ResponseDTO(
                'success',
                'Registrasi akun berhasil',
                [
                    'token' => $userResult['token'],
                ],
                null,
                201
            );
        } catch (Exception $e) {
            DB::rollBack();

            return new ResponseDTO(
                'error',
                'Registrasi gagal: ' . $e->getMessage(),
                null,
                [
                    'code' => $e->getCode(),
                ],
                500
            );
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
            return new ResponseDTO(
                'error',
                'Email tidak ditemukan.',
                null,
                [
                    'email' => $validatedData['email'],
                ],
                404
            );
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

            return new ResponseDTO(
                'success',
                'OTP untuk reset password telah dikirim ke email Anda.',
                [
                    'otp' => $otp['code'],
                    'temp_token' => $caching['token'],
                ],
                null,
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            return new ResponseDTO(
                'error',
                'Gagal mengirim OTP untuk reset password.',
                null,
                [
                    'detail' => $e->getMessage(),
                ],
                500
            );
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

        if (($verify['status'] ?? null) === 'success') {
            return new ResponseDTO(
                'success',
                (string) ($verify['message'] ?? ''),
                [
                    'token' => $tokenReset,
                ],
                null,
                (int) ($verify['code'] ?? 200)
            );
        }

        return new ResponseDTO(
            'error',
            (string) ($verify['message'] ?? ''),
            null,
            [
                'identifier' => $verify['identifier'] ?? null,
                'code' => $verify['code'] ?? null,
            ],
            (int) ($verify['code'] ?? 400)
        );
    }

    public function resetPassword(UpdateAuthRequest $request): ResponseDTO
    {
        $validatedData = $request->validated();

        $cacheKey = 'auth:reset-password:' . $validatedData['token'];
        $cacheData = Cache::get($cacheKey);
        if (!$cacheData || empty($cacheData['email'])) {
            return new ResponseDTO(
                'error',
                'Token reset tidak valid atau sudah kadaluarsa.',
                null,
                [
                    'token' => 'invalid',
                ],
                422
            );
        }

        $user = User::getUserByEmail($cacheData['email']);

        if (!$user->exists()) {
            return new ResponseDTO(
                'error',
                'Email tidak ditemukan.',
                null,
                [
                    'email' => $cacheData['email'] ?? null,
                ],
                404
            );
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

        return new ResponseDTO(
            'success',
            'Password berhasil direset.',
            [],
            null,
            200
        );
    }

}
