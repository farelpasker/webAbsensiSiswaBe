<?php

namespace App\Repositories;

use App\Models\LeaveRequest;

class LeaveRequestRepository
{
    private $model;

    public function __construct(LeaveRequest $model)
    {
        $this->model = $model;
    }

    public function paginate($page, $perPage, $search = null, $date = null)
    {
        $query = $this->model->query();

        if ($search) {
            $query->whereHas('student.user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        if ($date) {
            $query->where('start_date', $date);
        }

        return $query
        ->with('student:id,user_id,kelas_id','student.user:id,name,email,phone','student.kelas:id,nama')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function findById($id)
    {
        return $this->model
        ->with('student:id,user_id,kelas_id','student.user:id,name,email,phone','student.kelas:id,nama')
        ->findOrFail($id);
    }

    public function update($id, $data)
    {
        $leaveRequest = $this->findById($id);
        if ($leaveRequest) {
            $leaveRequest->update($data);
            return $leaveRequest;
        }
        return null;
    }

    public function delete($id)
    {
        $leaveRequest = $this->findById($id);
        if ($leaveRequest) {
            $leaveRequest->delete();
            return true;
        }
        return false;
    }

    public function studentHasPendingRequest($studentId, $fromDate)
    {
        return $this->model->where('student_id', $studentId)
            ->where('start_date', $fromDate)
            ->where('status', 'pending')
            ->exists();
    }

    public function paginateByStudent($studentId, $page, $perPage)
    {
        return $this->model
        ->where('student_id', $studentId)
        ->with('student:id,user_id,kelas_id','student.user:id,name,email,phone','student.kelas:id,nama')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);
    }

}