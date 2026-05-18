<?php

namespace App\Repositories;

use App\Models\Kelas;
use function Laravel\Prompts\search;

class KelasRepository
{
    private $model;

    public function __construct(Kelas $model)
    {
        $this->model = $model;
    }

    public function getList($params = [])
    {
        $query = $this->model->all();
        return $query->sortByDesc('created_at')->values();
    }

    public function paginate(int $page, int $perPage, $search = null)
    {
        $query = $this->model->query()->withCount('students');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getDetail($id, $fields = ['*'])
    {
        return $this->model->select($fields)->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $kelas = $this->model->findOrFail($id);
        $kelas->update($data);
        return $kelas;
    }

    public function delete(string $id)
    {
        $kelas = $this->model->findOrFail($id);
        return $kelas->delete();
    }

    public function getMyClasses($teacherId)
    {
        return $this->model->whereHas('teacherClassrooms', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->withCount('students')->get();
    }

    public function findClassByTeacher($teacherId, $kelasId)
    {
        return $this->model->where('id', $kelasId)->whereHas('teacherClassrooms', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->first();
    }
}