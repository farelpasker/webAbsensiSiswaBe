<?php

namespace App\Repositories;

use App\Exports\AttendanceExport;
use App\Exports\RecapExport;
use App\Exports\RecapByClassTeacherExport;
use App\Models\Attendance;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceRepository
{
    private $model;
    private $studentRepo;

    public function __construct(Attendance $model, StudentRepository $studentRepo)
    {
        $this->model = $model;
        $this->studentRepo = $studentRepo;
    }

    public function getList($params, int $page = 1, int $perPage = 10)
    {
        $query = $this->model->query()->with('student:id,nis,user_id,kelas_id','student.user:id,name','student.kelas:id,nama');

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

    public function exportExcelByAdmin($params) {
        $filename = 'attendance_export_' . now()->format('Ymd_His') . '.xlsx';
        
        // Download langsung ke user
        return Excel::download(
            new AttendanceExport($params), 
            $filename
        );
    }

    public function exportRecapExcelByAdmin($params) {
        $filename = 'recap_export_' . now()->format('Ymd_His') . '.xlsx';
        
        // Download langsung ke user
        return Excel::download(
            new RecapExport($params), 
            $filename
        );
    }

    public function recap(array $params) {

        $students = $this->studentRepo->getList()->get();

        return $students->map(function($student) use ($params) {
            $query = $this->model->query()->where('student_id', $student->id);

            if(isset($params['month']) && !empty($params['month'])) {
                $query->whereMonth('created_at', $params['month']);
            }

            if(isset($params['year']) && !empty($params['year'])) {
                $query->whereYear('created_at', $params['year']);
            }

            if(isset($params['kelas_id']) && !empty($params['kelas_id'])) {
                $query->whereHas('student', function($q) use ($params) {
                    $q->where('kelas_id', $params['kelas_id']);
                });
            }

            return [
                'siswa_id' => $student->id,
                'siswa_name' => $student->user->name,
                'total' => (clone $query)->count(),
                'hadir' => (clone $query)->where('status', 'hadir')->count(),
                'izin' => (clone $query)->where('status', 'izin')->count(),
                'sakit' => (clone $query)->where('status', 'sakit')->count(),
                'alpha' => (clone $query)->where('status', 'tidak hadir')->count(),
                'libur' => (clone $query)->where('status', 'libur')->count(),
                'terlambat' => (clone $query)->where('status', 'telat')->count(),
            ];
        });
    }

    public function recapClassByTeacher($teacherId, array $params, $kelasId) {
        $students = $this->studentRepo->getList()->whereHas('kelas.teacherClassrooms', function($q) use ($teacherId, $kelasId) {
            $q->where('teacher_id', $teacherId)
              ->where('kelas_id', $kelasId);
        })->get();

        return $students->map(function($student) use ($params) {
            $query = $this->model->query()->where('student_id', $student->id);
            
            if(isset($params['month']) && !empty($params['month'])) {
                $query->whereMonth('created_at', $params['month']);
            }

            if(isset($params['year']) && !empty($params['year'])) {
                $query->whereYear('created_at', $params['year']);
            }

            return [
                'siswa_id' => $student->id,
                'siswa_name' => $student->user->name,
                'total' => (clone $query)->count(),
                'hadir' => (clone $query)->where('status', 'hadir')->count(),
                'izin' => (clone $query)->where('status', 'izin')->count(),
                'sakit' => (clone $query)->where('status', 'sakit')->count(),
                'alpha' => (clone $query)->where('status', 'tidak hadir')->count(),
                'libur' => (clone $query)->where('status', 'libur')->count(),
                'terlambat' => (clone $query)->where('status', 'telat')->count(),
            ];
        });
    }

    public function exportRecapClassByTeacher($teacherId, array $params, $kelasId) {
        $filename = 'recap_class_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(
            new RecapByClassTeacherExport($teacherId, $params, $kelasId),
            $filename
        );
    }
}