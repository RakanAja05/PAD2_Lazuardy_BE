<?php

namespace App\Services\Auth;

use App\DTOs\ResponseDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthService
{
    public function redirectToGoogle(): ResponseDTO
    {
        $response = Socialite::driver('google')->redirect();

        return new ResponseDTO(
            'success',
            'Redirect to Google',
            $response,
            null,
            $response->getStatusCode()
        );
    }

    public function handleGoogleCallback(): ResponseDTO
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {
                if (empty($existingUser->google_id)) {
                    $existingUser->google_id = $googleUser->getId();
                    $existingUser->save();
                }

                $existingUser->tokens()->delete();
                $token = $existingUser->createToken('auth_token')->plainTextToken;

                return new ResponseDTO(
                    'success',
                    'Login berhasil',
                    [
                        'type' => 'login',
                        'token' => $token,
                        'user' => [
                            'id' => $existingUser->id,
                            'name' => $existingUser->name,
                            'email' => $existingUser->email,
                            'role' => $existingUser->role,
                        ],
                    ],
                    null,
                    200
                );
            }

            return new ResponseDTO(
                'success',
                'User belum terdaftar, silakan lengkapi data',
                [
                    'type' => 'register',
                    'google_data' => [
                        'google_id' => $googleUser->getId(),
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'avatar' => $googleUser->getAvatar(),
                    ],
                ],
                null,
                200
            );
        } catch (\Exception $e) {
            return new ResponseDTO(
                'error',
                'Gagal login dengan Google: ' . $e->getMessage(),
                null,
                [
                    'detail' => $e->getMessage(),
                ],
                500
            );
        }
    }

    public function completeGoogleRegistration(Request $request): ResponseDTO
    {
        $validated = $request->validate([
            'google_id' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:student,tutor',
            'telephone_number' => 'nullable|string|max:15',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'profile_photo_path' => 'nullable|string',
        ]);

        try {
            $user = User::create([
                'google_id' => $validated['google_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'telephone_number' => $validated['telephone_number'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'profile_photo_path' => $validated['profile_photo_path'] ?? null,
                'email_verified_at' => now(),
                'password' => Hash::make(uniqid()),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return new ResponseDTO(
                'success',
                'Registrasi berhasil',
                [
                    'user' => $user,
                    'token' => $token,
                ],
                null,
                201
            );
        } catch (\Exception $e) {
            return new ResponseDTO(
                'error',
                'Gagal menyelesaikan registrasi',
                null,
                [
                    'detail' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
