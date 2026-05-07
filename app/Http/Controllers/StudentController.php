<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Repositories\StudentRepository;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    private $repo;
    public function __construct(StudentRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request) {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'kelas_id' => 'nullable|exists:kelas,id',
            'search' => 'nullable|string|max:255',
        ]);
        
        try {
            $params = $request->only(['kelas_id', 'search']);
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;

            $data = $this->repo->paginate($params, $page, $perPage);
            return ApiResponse::Paginate(
                $data->items(),
                "Daftar siswa berhasil diambil",
                PaginationHelper::meta($data)
            );
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage(), 400);
        }
    }

    public function show($id) {
        try {
            $student = $this->repo->findById($id);
            return ApiResponse::Create("Data siswa berhasil diambil", $student);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage(), 404);
        }
    }

    public function registerFace(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|array',
        ]);

        try {
            $user = auth()->user();
            if (!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }

            if (!$user->hasRole('student')) {
                return ApiResponse::Custom(false, 'Hanya siswa yang dapat melakukan registrasi wajah', null, 403);
            }

            $student = $user->student;
            if (!$student) {
                return ApiResponse::Custom(false, 'Data siswa tidak ditemukan', null, 404);
            }

            $this->repo->saveDescriptor($request->face_descriptor, $student);
            return ApiResponse::Create("Face descriptor berhasil disimpan", null);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage(), 500);
        }
    }

    public function myFace(Request $request) {
        try {
            $user = auth()->user();
            if (!$user) {
                return ApiResponse::Custom(false, 'User tidak ditemukan', null, 404);
            }

            if (!$user->hasRole('student')) {
                return ApiResponse::Custom(false, 'Hanya siswa yang dapat melihat face descriptor', null, 403);
            }

            $student = $user->student;
            if (!$student) {
                return ApiResponse::Custom(false, 'Data siswa tidak ditemukan', null, 404);
            }

            return ApiResponse::Success(json_decode($student->face_descriptor),"Face descriptor berhasil diambil", 200);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage(), 500);
        }
    }
}
