<?php

namespace App\Repositories;

use App\Models\Attendance;

class AttendanceRepository
{
    private $model;

    public function __construct(Attendance $model)
    {
        $this->model = $model;
    }

    public function getList($search = null, int $page = 1, int $perPage = 10)
    {
        $query = $this->model->query();

        if($search) {
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getDetail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $attendance = $this->model->findOrFail($id);
        $attendance->update($data);
        return $attendance;
    }

    public function delete($id)
    {
        $attendance = $this->model->findOrFail($id);
        $attendance->delete();
        return true;
    }

    public function isAttended($studentId, $date)
    {
        return $this->model->where('student_id', $studentId)
            ->where('date', $date)
            ->exists();
    }

    public function meAttendance($studentId, int $page = 1, int $perPage = 10)
    {
        return $this->model->where('student_id', $studentId)
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getTodayAttendance($studentId)
    {
        return $this->model->where('student_id', $studentId)
            ->where('date', now()->toDateString())
            ->first();
    }

    public function calender($studentId, $month, $year)
    {
        return $this->model
            ->select('date', 'status')
            ->where('student_id', $studentId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();
    }
}