<?php

namespace App\Services\SocialAuth;

use App\Models\User;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthService
{
    public function redirectToProvider(string $provider): RedirectResponse
    {
        $allowedProviders = ['google', 'facebook'];
        abort_unless(in_array($provider, $allowedProviders, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        $allowedProviders = ['google', 'facebook'];
        if (!in_array($provider, $allowedProviders, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider tidak didukung',
                'errors' => [
                    'code' => 'provider_not_supported',
                    'provider' => $provider,
                ],
            ], 400);
        }

        if (request()->missing('code') && request()->missing('oauth_token') && request()->missing('access_token')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter OAuth tidak lengkap',
                'errors' => [
                    'code' => 'oauth_missing_params',
                ],
            ], 400);
        }

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver($provider);
            $socialiteUser = $driver->stateless()->user();

            $providerIdColumn = $provider . '_id';
            $providerUserId = (string) $socialiteUser->getId();

            // Only these providers are considered to have a trusted/verified email.
            // For providers outside this list (e.g., Facebook), do NOT auto-link by email.
            $trustedEmailProviders = ['google'];

            // Priority 1: match by provider id
            $user = User::query()->where($providerIdColumn, $providerUserId)->first();

            // Priority 2 (trusted providers only): match by email (then link provider id if not linked yet)
            if (!$user) {
                $email = $socialiteUser->getEmail();
                if ($email !== null) {
                    if (in_array($provider, $trustedEmailProviders, true)) {
                        $user = User::query()->where('email', $email)->first();

                        if ($user) {
                            $existingProviderId = $user->{$providerIdColumn} ?? null;
                            if (!empty($existingProviderId) && (string) $existingProviderId !== $providerUserId) {
                                return response()->json([
                                    'status' => 'error',
                                    'message' => 'Akun sudah terhubung dengan akun ' . ucfirst($provider) . ' yang lain',
                                    'errors' => [
                                        'code' => 'provider_conflict',
                                        'provider' => $provider,
                                    ],
                                ], 409);
                            }

                            if (empty($existingProviderId)) {
                                $user->{$providerIdColumn} = $providerUserId;
                                $user->save();
                            }
                        }
                    } else {
                        $existingByEmail = User::query()->where('email', $email)->first();
                        if ($existingByEmail) {
                            return response()->json([
                                'status' => 'error',
                                'message' => 'Email sudah terdaftar. Silakan login menggunakan metode yang sudah terhubung.',
                                'errors' => [
                                    'code' => 'email_already_registered',
                                    'provider' => $provider,
                                ],
                            ], 409);
                        }
                    }
                }
            }

            if ($user) {
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login menggunakan ' . ucfirst($provider) . ' berhasil.',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                        'token_type' => 'Bearer',
                    ],
                ], 200);
            }

            // User belum terdaftar -> arahkan ke register (prefill email, provider, username)
            $providerEmail = $socialiteUser->getEmail();
            $userEmail = $providerEmail ?? $providerUserId . '@' . $provider . '.local';
            $emailLocked = $providerEmail !== null && in_array($provider, $trustedEmailProviders, true);

            $username = $socialiteUser->getName();
            if (!$username && method_exists($socialiteUser, 'getNickname')) {
                $username = $socialiteUser->getNickname();
            }
            if (!$username && $providerEmail) {
                $username = Str::before($providerEmail, '@');
            }

            $tempToken = Str::random(40);
            Cache::put('social:register:' . $tempToken, [
                'provider' => $provider,
                'provider_id' => $providerUserId,
                'email' => $userEmail,
                'email_locked' => $emailLocked,
                // Backward-compatible keys
                'name' => $username,
                'username' => $username,
                'avatar' => $socialiteUser->getAvatar(),
            ], 1800);

            $frontend = config('app.frontend_url') ?? env('FRONTEND_URL');
            $payload = json_encode([
                'provider' => $provider,
                'email' => $userEmail,
                'username' => $username,
                // Backward-compatible key
                'name' => $username,
                'temp_token' => $tempToken,
                'email_locked' => $emailLocked,
            ]);

            if ($frontend) {
                $redirectUrl = rtrim($frontend, '/') . '/register?social_data=' . urlencode($payload);
                return redirect()->away($redirectUrl);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Akun belum terdaftar, silakan lengkapi data',
                'data' => [
                    'type' => 'register',
                    'provider' => $provider,
                    'email' => $userEmail,
                    'username' => $username,
                    // Backward-compatible key
                    'name' => $username,
                    'temp_token' => $tempToken,
                    'email_locked' => $emailLocked,
                ],
            ], 200);
        } catch (ClientException $e) {
            $status = $e->getResponse()?->getStatusCode() ?? 400;
            $payload = [
                'status' => 'error',
                'message' => 'Gagal otentikasi melalui ' . ucfirst($provider) . '.',
                'errors' => [
                    'code' => 'oauth_provider_error',
                    'provider' => $provider,
                ],
            ];

            if (config('app.debug')) {
                $payload['errors']['detail'] = $e->getMessage();
            }

            return response()->json($payload, $status);
        } catch (Throwable $e) {
            $payload = [
                'status' => 'error',
                'message' => 'Gagal otentikasi melalui ' . ucfirst($provider) . '.',
                'errors' => [
                    'code' => 'oauth_unexpected_error',
                    'provider' => $provider,
                ],
            ];

            if (config('app.debug')) {
                $payload['errors']['detail'] = $e->getMessage();
            }

            return response()->json($payload, 500);
        }
    }
}
