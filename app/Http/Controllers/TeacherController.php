<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Http\Requests\ParentRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private $repo;
    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
        ]);
        try {
            $search = $request->search;
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 10;

            $data = $this->repo->getListByTeacher($search, $page, $perPage);
            return ApiResponse::Paginate($data->items(), "Berhasil mengambil data guru", PaginationHelper::meta($data));
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal mengambil data guru: " . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ParentRequest $request)
    {
        try {
            $data = $request->validated();

            $teacher = $this->repo->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'phone' => $data['phone'],
            ]);

            $teacher->assignRole('teacher');
            return ApiResponse::Create("Guru berhasil dibuat", $teacher);
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal membuat guru: " . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $teacher = $this->repo->teacherById($id);
            return ApiResponse::Success($teacher, "Berhasil mengambil data guru");
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal mengambil data guru: " . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ParentRequest $request, string $id)
    {
        try {
            $data = $request->validated();
            $teacher = $this->repo->update($id, [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'phone' => $data['phone'],
            ]);
            return ApiResponse::Success($teacher, "Guru berhasil diperbarui");
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal memperbarui guru: " . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $teacher = $this->repo->teacherById($id);
            $teacher->delete();
            return ApiResponse::Success($teacher, "Guru berhasil dihapus");
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal menghapus guru: " . $e->getMessage());
        }
    }

    public function assignClassroom(Request $request, string $teacherId)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'subject' => 'required|string|max:255',
        ]);

        try {
            $teacher = $this->repo->teacherById($teacherId);
            if(!$teacher) {
                return ApiResponse::Error("Guru tidak ditemukan", 404);
            }

            $teacher->teacherClassrooms()->create([
                'kelas_id' => $request->kelas_id,
                'subject' => $request->subject,
            ]);

            return ApiResponse::Success(null, "Kelas berhasil ditugaskan ke guru " . $teacher->name);
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal menugaskan kelas ke guru: " . $e->getMessage());
        }
    }
}
