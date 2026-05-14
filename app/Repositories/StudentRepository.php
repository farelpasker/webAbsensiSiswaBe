<?php

namespace App\Repositories;

use App\Models\Student;

class StudentRepository
{
    private $model;

    public function __construct(Student $model)
    {
        $this->model = $model;
    }

    public function paginate(array $data,int $page,int $perPage)
    {
        $query = $this->model->query();

        if (isset($data['search'])) {
            $query->where('nis', 'like', '%' . $data['search'] . '%')
                ->orWhereHas('user', function ($q) use ($data) {
                    $q->where('name', 'like', '%' . $data['search'] . '%');
                });
        }

        if(isset($data['kelas_id'])) {
            // Validasi kelas_id ada di database
            if (!\DB::table('kelas')->where('id', $data['kelas_id'])->exists()) {
                throw new \Exception('Kelas tidak ditemukan');
            }
            $query->where('kelas_id', $data['kelas_id']);
        }

        return $query
        ->with('user:id,name,email,phone','kelas:id,nama','parent:id,name,email,phone')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById($id)
    {
        return $this->model
        ->with('user:id,name,email,phone','kelas:id,nama','parent:id,name,email,phone')
        ->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $student = $this->findById($id);
        $student->update($data);
        return $student;
    }

    public function delete($id)
    {
        $student = $this->findById($id);
        $student->delete();
        return true;
    }

    public function saveDescriptor(array $descriptor, $student)
    {
        $student->update([
            'face_descriptor' => json_encode($descriptor)
            ]);
        return $student;
    }
}