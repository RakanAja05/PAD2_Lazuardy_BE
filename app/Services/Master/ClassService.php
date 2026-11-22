<?php

namespace App\Services\Master;

use App\DTOs\ResponseDTO;
use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassService
{
    public function index(): ResponseDTO
    {
        $classes = ClassModel::query()
            ->orderByRaw("FIELD(level, 'SD', 'SMP', 'SMA')")
            ->orderBy('name')
            ->get();

        return new ResponseDTO('success', 'Data kelas berhasil diambil', ['classes' => $classes], null, 200);
    }

    public function show(int $id): ResponseDTO
    {
        $class = ClassModel::query()->with('subjects')->find($id);

        if (!$class) {
            return new ResponseDTO('error', 'Kelas tidak ditemukan', null, ['id' => 'not_found'], 404);
        }

        return new ResponseDTO('success', 'Data kelas berhasil diambil', ['class' => $class], null, 200);
    }

    public function store(Request $request): ResponseDTO
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'level' => ['required', 'string', 'in:SD,SMP,SMA'],
        ]);

        $class = ClassModel::query()->create($data);

        return new ResponseDTO('success', 'Kelas berhasil ditambahkan', ['class' => $class], null, 201);
    }

    public function update(Request $request, int $id): ResponseDTO
    {
        $class = ClassModel::query()->find($id);

        if (!$class) {
            return new ResponseDTO('error', 'Kelas tidak ditemukan', null, ['id' => 'not_found'], 404);
        }

        $data = $request->validate([
            'name'  => ['sometimes', 'string', 'max:100'],
            'level' => ['sometimes', 'string', 'in:SD,SMP,SMA'],
        ]);

        $class->update($data);

        return new ResponseDTO('success', 'Kelas berhasil diperbarui', ['class' => $class], null, 200);
    }

    public function destroy(int $id): ResponseDTO
    {
        $class = ClassModel::query()->find($id);

        if (!$class) {
            return new ResponseDTO('error', 'Kelas tidak ditemukan', null, ['id' => 'not_found'], 404);
        }

        $class->delete();

        return new ResponseDTO('success', 'Kelas berhasil dihapus', null, null, 200);
    }
}
