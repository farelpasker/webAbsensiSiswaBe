<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    private $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function listUsers(int $page, int $perPage)
    {
        return $this->model->with('roles:id,name')->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
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
        $user = $this->model->findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete(string $id)
    {
        $user = $this->model->findOrFail($id);
        
        $user->syncRoles([]);
        $user->syncPermissions([]);
        
        return $user->delete();
    }

    public function getListByParent($params, $page, $perPage)
    {
        $query = $this->model
        ->whereHas('roles', function($q) {
            $q->where('name', 'parent');
        });

        if (isset($params['kelas_id'])) {
            $query->whereHas('children', function($q) use ($params) {
                $q->where('kelas_id', $params['kelas_id']);
            });
        }

        if (isset($params['search'])) {
            $query->where('name', 'like', '%' . $params['search'] . '%');
        }

        return $query->with(['children:id,user_id,kelas_id,parent_id','children.user:id,name,email,phone'])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);
    }

    public function parentById($id)
    {
        return $this->model->whereHas('roles', function($q) {
            $q->where('name', 'parent');
        })->with('children.user:id,name,email,phone')->findOrFail($id);
    }

    public function getListByTeacher($search, $page, $perPage)
    {
        $query = $this->model
        ->whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        });

        if (isset($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->with(['teacherClassrooms.kelas:id,nama'])->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    public function teacherById($id)
    {
        return $this->model->whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        })->with(['teacherClassrooms.kelas:id,nama'])->findOrFail($id);
    }
}