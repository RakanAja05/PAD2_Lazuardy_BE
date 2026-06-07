<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Master\ClassService;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function __construct(private readonly ClassService $classService) {}

    /**
     * @OA\Get(
     *     path="/api/classes",
     *     tags={"Master"},
     *     summary="List semua kelas",
     *     @OA\Response(response=200, description="List kelas", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function index()
    {
        return $this->respond($this->classService->index());
    }

    /**
     * @OA\Get(
     *     path="/api/classes/{id}",
     *     tags={"Master"},
     *     summary="Detail kelas beserta subjects",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail kelas", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function show(int $id)
    {
        return $this->respond($this->classService->show($id));
    }

    /**
     * @OA\Post(
     *     path="/api/classes",
     *     tags={"Master"},
     *     summary="Tambah kelas (admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","level"},
     *             @OA\Property(property="name", type="string", example="Kelas 7"),
     *             @OA\Property(property="level", type="string", enum={"SD","SMP","SMA"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Kelas ditambahkan", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function store(Request $request)
    {
        return $this->respond($this->classService->store($request));
    }

    /**
     * @OA\Patch(
     *     path="/api/classes/{id}",
     *     tags={"Master"},
     *     summary="Edit kelas (admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Kelas diperbarui", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function update(Request $request, int $id)
    {
        return $this->respond($this->classService->update($request, $id));
    }

    /**
     * @OA\Delete(
     *     path="/api/classes/{id}",
     *     tags={"Master"},
     *     summary="Hapus kelas (admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Kelas dihapus", @OA\JsonContent(ref="#/components/schemas/StandardSuccess"))
     * )
     */
    public function destroy(int $id)
    {
        return $this->respond($this->classService->destroy($id));
    }
}
