<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Repositories\KelasRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    private $repo;
    public function __construct(KelasRepository $repo){
        $this->repo = $repo;
    }

    public function index(Request $request) {
        $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
        ]);

        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $search = $request->search;

        $data = $this->repo->paginate($page, $perPage, $search);
        return ApiResponse::Paginate($data->items(), "Berhasil mengambil data kelas", PaginationHelper::meta($data));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $kelas = $this->repo->create((['nama' => $request->name]));
            return ApiResponse::Create("Kelas berhasil dibuat", $kelas);
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal membuat kelas: " . $e->getMessage());
        }
    }

    public function show($id) {
        try {
            $kelas = $this->repo->getDetail($id);
            return ApiResponse::Success($kelas, "Berhasil mengambil data kelas");
        } catch (ModelNotFoundException $e) {
            return ApiResponse::Error("Kelas tidak ditemukan");
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal mengambil data kelas: " . $e->getMessage());
        }
    }

    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $kelas = $this->repo->update($id, ['nama' => $request->name]);
            return ApiResponse::Success($kelas, "Kelas berhasil diperbarui");
        } catch (ModelNotFoundException $e) {
            return ApiResponse::Error("Kelas tidak ditemukan");
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal memperbarui kelas: " . $e->getMessage());
        }
    }

    public function destroy($id) {
        try {
            $this->repo->delete($id);
            return ApiResponse::Success(null, "Kelas berhasil dihapus");
        } catch (ModelNotFoundException $e) {
            return ApiResponse::Error("Kelas tidak ditemukan");
        } catch (\Exception $e) {
            return ApiResponse::Error("Gagal menghapus kelas: " . $e->getMessage());
        }
    }
}
