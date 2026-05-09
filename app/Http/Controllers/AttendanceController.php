<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Repositories\AttendanceRepository;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private $service;
    private $repo;

    public function __construct(AttendanceService $service, AttendanceRepository $repo)
    {
        $this->service = $service;
        $this->repo = $repo;
    }

    public function absen(Request $request) {
        
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
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
    
            $data = $this->service->recordAttendance($student, $request->latitude, $request->longitude);
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
}
