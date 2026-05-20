<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Repositories\AttendanceRepository;
use App\Repositories\KelasRepository;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private $service;
    private $repo;
    private $kelasRepo;

    public function __construct(AttendanceService $service, AttendanceRepository $repo, KelasRepository $kelasRepo)
    {
        $this->service = $service;
        $this->repo = $repo;
        $this->kelasRepo = $kelasRepo;
    }

    public function index(Request $request) {
        try {
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;
            $params = $request->only(['search','date_from','date_to','status','kelas_id']);
            $result = $this->repo->getList($params, $page, $perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data absensi berhasil diambil',
                'data' => $result['paginate']->items(),
                'summary' => [
                    'total_student' => $result['total_student'],
                    'hadir_count' => $result['hadir_count'],
                    'tidak_hadir_count' => $result['tidak_hadir_count']
                ],
                'paginate' => PaginationHelper::meta($result['paginate'])
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }
    public function indexTodayAdmin(Request $request) {
        try {
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;
            $params = $request->only(['search','date_from','date_to','status','kelas_id']);
            $result = $this->repo->getListTodayAdmin($params, $page, $perPage);
            
            return ApiResponse::Paginate($result->items(), 'Data absensi hari ini berhasil diambil', PaginationHelper::meta($result));
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function listByTeacher(Request $request) {
        try {
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;
            $params = $request->only(['search','date_from','date_to','status']);
            $user = auth()->user();

            if(!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }

            if(!$user->hasRole('teacher')) {
                return ApiResponse::Custom(false, 'Hanya guru yang dapat melihat data absensi', null, 403);
            }

            $teacher = $user;
            if(!$teacher->teacherClassrooms()->exists()) {
                return ApiResponse::Custom(false, 'Anda belum memiliki kelas yang diajar', null, 404);
            }
            $data = $this->repo->getListByTeacher($teacher->id, $params, $page, $perPage);
            return ApiResponse::Paginate($data->items(), 'Data absensi berhasil diambil', PaginationHelper::meta($data));
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function absen(Request $request) {
        
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'face_descriptor' => 'required|array',
        ],
        [
            'latitude.required' => 'Latitude wajib diisi',
            'latitude.numeric' => 'Latitude harus berupa angka',
            'longitude.required' => 'Longitude wajib diisi',
            'longitude.numeric' => 'Longitude harus berupa angka',
            'face_descriptor.required' => 'Face descriptor wajib diisi',
            'face_descriptor.array' => 'Face descriptor harus berupa array',
        ]);

        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }
    
            if (!$user->hasRole('student')) {
                return ApiResponse::Custom(false, 'Hanya siswa yang dapat melakukan absensi', null, 403);
            }
    
            $student = $user->student;
            if (!$student) {
                return ApiResponse::Custom(false, 'Data siswa tidak ditemukan', null, 404);
            }
    
            $data = $this->service->recordAttendance($student, $request->latitude, $request->longitude, $request->face_descriptor);
            DB::commit();
            return ApiResponse::Create('Absensi berhasil disimpan', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function me(Request $request) {
        try {
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;
            $user = $request->user();

            if(!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }

            if(!$user->hasRole('student')) {
                return ApiResponse::Custom(false, 'Hanya siswa yang dapat melihat data absensi', null, 403);
            }

            $student = $user->student;
            if(!$student) {
                return ApiResponse::Custom(false, 'Data siswa tidak ditemukan', null, 404);
            }

            $data = $this->repo->meAttendance($student->id, $page, $perPage);
            return ApiResponse::Paginate($data->items(), 'Data user berhasil diambil', PaginationHelper::meta($data));
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function today(Request $request) {
        try {
            $user = $request->user();
            if(!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }

            if(!$user->hasRole('student')) {
                return ApiResponse::Custom(false, 'Hanya siswa yang dapat melihat data absensi', null, 403);
            }

            $student = $user->student;
            if(!$student) {
                return ApiResponse::Custom(false, 'Data siswa tidak ditemukan', null, 404);
            }

            $data = $this->repo->getTodayAttendance($student->id);
            
            if(!$data) {
                return ApiResponse::Notfound('Anda belum melakukan absensi hari ini');
            }
            return ApiResponse::Success($data, 'Data absensi hari ini berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function calender(Request $request) {
        try {
            $month = $request->month ?? now()->month;
            $year = $request->year ?? now()->year;
            $user = $request->user();
            if(!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }

            if(!$user->hasRole('student')) {
                return ApiResponse::Custom(false, 'Hanya siswa yang dapat melihat data absensi', null, 403);
            }

            $student = $user->student;
            if(!$student) {
                return ApiResponse::Custom(false, 'Data siswa tidak ditemukan', null, 404);
            }

            $data = $this->repo->calender($student->id, $month, $year);
            
            return ApiResponse::Success($data, 'Data absensi berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function exportExcelByAdmin(Request $request) {
        try {
            $params = $request->only(['month', 'year', 'kelas_id', 'status']);
            return $this->repo->exportExcelByAdmin($params);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function exportRecapExcelByAdmin(Request $request) {
        try {
            $params = $request->only(['month', 'year', 'kelas_id', 'status']);
            return $this->repo->exportRecapExcelByAdmin($params);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function recap(Request $request) {
        try {
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;
            $params = $request->only(['month', 'year', 'kelas_id', 'status', 'search']);
            $data = $this->repo->recap($params, $page, $perPage);
            return ApiResponse::Paginate($data->items(), 'Rekap absensi berhasil diambil', PaginationHelper::meta($data));
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function recapClassByTeacher(Request $request, $kelasId) {
        try {
            $user = auth()->user();
            if(!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }

            if(!$user->hasRole('teacher')) {
                return ApiResponse::Custom(false, 'Hanya guru yang dapat melihat rekap absensi', null, 403);
            }

            $teacher = $user;
            if(!$teacher->teacherClassrooms()->exists()) {
                return ApiResponse::Custom(false, 'Anda belum memiliki kelas yang diajar', null, 404);
            }

            $kelas = $this->kelasRepo->findClassByTeacher($teacher->id, $kelasId);
            if(!$kelas) {
                return ApiResponse::Custom(false, 'Anda tidak mengajar kelas ini', null, 404);
            }

            $params = $request->only(['month', 'year']);
            $data = $this->repo->recapClassByTeacher($teacher->id, $params, $kelasId);
            return ApiResponse::Success($data, 'Rekap absensi berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    public function exportRecapClassByTeacher(Request $request, $kelasId) {
        try {
            $user = auth()->user();
            if(!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }

            if(!$user->hasRole('teacher')) {
                return ApiResponse::Custom(false, 'Hanya guru yang dapat mengexport rekap absensi', null, 403);
            }

            $teacher = $user;
            if(!$teacher->teacherClassrooms()->exists()) {
                return ApiResponse::Custom(false, 'Anda belum memiliki kelas yang diajar', null, 404);
            }

            $kelas = $this->kelasRepo->findClassByTeacher($teacher->id, $kelasId);
            if(!$kelas) {
                return ApiResponse::Custom(false, 'Anda tidak mengajar kelas ini', null, 404);
            }

            $params = $request->only(['month', 'year']);
            return $this->repo->exportRecapClassByTeacher($teacher->id, $params, $kelasId);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }
}
