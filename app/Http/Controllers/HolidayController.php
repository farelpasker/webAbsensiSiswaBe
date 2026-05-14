<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Http\Requests\HolidayRequest;
use App\Repositories\HolidayRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    private $repo;
    public function __construct(HolidayRepository $repo){
        $this->repo = $repo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'search' => 'string|max:255',
        ]);
        try {
            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;
            $search = $validated['search'] ?? null;

            $data = $this->repo->getList($search, $page, $perPage);
            return ApiResponse::Paginate(
                $data->items(),
                'Daftar hari libur berhasil diambil',
                PaginationHelper::meta($data)
                );
        } catch (\Exception $e) {
            return ApiResponse::Error('Gagal mengambil daftar hari libur: ' . $e->getMessage(), 400);
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
    public function store(HolidayRequest $request)
    {
        $validated = $request->validated();

        try {
            $holiday = $this->repo->create($validated);
            return ApiResponse::Create('Hari libur berhasil dibuat', $holiday);
        } catch (\Exception $e) {
            return ApiResponse::Error('Gagal membuat hari libur: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $holiday = $this->repo->getDetail($id);
            return ApiResponse::Create('Hari libur berhasil diambil', $holiday);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::Notfound('Hari libur tidak ditemukan');
        } catch (\Exception $e) {
            return ApiResponse::Error('Gagal mengambil hari libur: ' . $e->getMessage(), 400);
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
    public function update(HolidayRequest $request, string $id)
    {
        $validated = $request->validated();

        try {
            $holiday = $this->repo->update($id, $validated);
            return ApiResponse::Success($holiday, 'Hari libur berhasil diperbarui');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::Notfound('Hari libur tidak ditemukan');
        } catch (\Exception $e) {
            return ApiResponse::Error('Gagal memperbarui hari libur: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->repo->delete($id);
            return ApiResponse::Success(null, 'Hari libur berhasil dihapus');
        } catch (ModelNotFoundException $e) {
            return ApiResponse::Notfound('Hari libur tidak ditemukan');
        } catch (\Exception $e) {
            return ApiResponse::Error('Gagal menghapus hari libur: ' . $e->getMessage(), 400);
        }
    }

}
