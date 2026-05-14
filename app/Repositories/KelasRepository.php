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

    public function paginate(int $page, int $perPage, $search = null)
    {
        $query = $this->model->query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
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
}