<?php

namespace App\Http\Controllers;

use App\DTOs\ResponseDTO;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/navigation",
     *     tags={"Dashboard"},
     *     summary="Get navigation items for current user",
     *     @OA\Response(response=200, description="Navigation items", @OA\JsonContent(ref="#/components/schemas/SuccessResponse"))
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user?->role?->value ?? 'guest';

        $items = [];

        // Common
        $items[] = ['title' => 'Home', 'path' => '/'];
        $items[] = ['title' => 'Cari Tutor', 'path' => '/tutors'];

        if ($role === 'guest') {
            $items[] = ['title' => 'Masuk', 'path' => '/login'];
        }

        if ($role === 'student') {
            $items[] = ['title' => 'Dashboard Siswa', 'path' => '/dashboard/student'];
            $items[] = ['title' => 'Jadwal Saya', 'path' => '/schedule'];
            $items[] = ['title' => 'Profil', 'path' => '/me'];
        }

        if ($role === 'tutor') {
            $items[] = ['title' => 'Dashboard Tutor', 'path' => '/dashboard/tutor'];
            $items[] = ['title' => 'Pengajuan', 'path' => '/tutor/requests'];
            $items[] = ['title' => 'Jadwal Saya', 'path' => '/tutor/schedule'];
            $items[] = ['title' => 'Profil', 'path' => '/me'];
        }

        if ($role === 'admin') {
            $items[] = ['title' => 'Admin', 'path' => '/admin'];
        }

        return $this->respond(new ResponseDTO('success', 'Navigation items', ['navigation' => $items], null, 200));
    }
}
