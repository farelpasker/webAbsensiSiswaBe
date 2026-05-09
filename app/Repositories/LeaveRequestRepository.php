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

    public function paginate($params, $page, $perPage)
    {
        $query = $this->model->query();

        if (isset($params['student_id'])) {
            $query->where('student_id', $params['student_id']);
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function findById($id)
    {
        return $this->model->find($id);
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

    public function studentHasPendingRequest($studentId, $date)
    {
        return $this->model->where('student_id', $studentId)
            ->where('date', $date)
            ->where('status', 'pending')
            ->exists();
    }

}