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

    public function getList($params, int $page = 1, int $perPage = 10)
    {
        $query = $this->model->query();

        if(isset($params['search']) && !empty($params['search'])) {
            $query->whereHas('student', function($q) use ($params) {
                $q->where('name', 'like', '%' . $params['search'] . '%');
            });
        }

        if(isset($params['date_from']) && isset($params['date_to'])) {
            $query->whereBetween('date', [$params['date_from'], $params['date_to']]);
        } elseif (isset($params['date_from'])) {
            $query->where('date', '>=', $params['date_from']);
        } elseif (isset($params['date_to'])) {
            $query->where('date', '<=', $params['date_to']);
        }

        if(isset($params['status']) && !empty($params['status'])) {
            $query->where('status', $params['status']);
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

    public function getListByTeacher($teacherId, $params, int $page = 1, int $perPage = 10)
    {
        $query = $this->model->whereHas('student', function($q) use ($teacherId) {
            $q->whereHas('kelas', function($q2) use ($teacherId) {
                $q2->whereHas('teacherClassrooms', function($q3) use ($teacherId) {
                    $q3->where('teacher_id', $teacherId);
                });
            });
        });

        if(isset($params['search']) && !empty($params['search'])) {
            $query->whereHas('student', function($q) use ($params) {
                $q->where('name', 'like', '%' . $params['search'] . '%');
            });
        }

        if(isset($params['date_from']) && isset($params['date_to'])) {
            $query->whereBetween('date', [$params['date_from'], $params['date_to']]);
        } elseif (isset($params['date_from'])) {
            $query->where('date', '>=', $params['date_from']);
        } elseif (isset($params['date_to'])) {
            $query->where('date', '<=', $params['date_to']);
        }

        if(isset($params['status']) && !empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}