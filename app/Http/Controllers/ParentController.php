<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\PaginationHelper;
use App\Http\Requests\ParentRequest;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ParentController extends Controller
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
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $params = $request->only(['search','kelas_id']);
        $data = $this->repo->getListByParent($params, $page, $perPage);
        return ApiResponse::Paginate(
            $data->items(),
            "Daftar orang tua berhasil diambil",
            PaginationHelper::meta($data)
        );
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
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $parent = $this->repo->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'phone' => $data['phone'] ?? null,
            ]);
            $parent->assignRole('parent');
            DB::commit();
            return ApiResponse::Create("Orang tua berhasil dibuat", $parent);
        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::Error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = $this->repo->parentById($id);
            return ApiResponse::Success("Data orang tua berhasil diambil", $data);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::Error("Orang tua tidak ditemukan", 404);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage(), 500);
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
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = bcrypt($data['password']);
            }
            
            $parent = $this->repo->update($id, $updateData);
            DB::commit();
            return ApiResponse::Success("Orang tua berhasil diperbarui", $parent);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            return ApiResponse::Error("Orang tua tidak ditemukan", 404);
        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::Error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $this->repo->delete($id);
            DB::commit();
            return ApiResponse::Success("Orang tua berhasil dihapus", null);
        } catch (ModelNotFoundException $e) {
            DB::rollback();
            return ApiResponse::Error("Orang tua tidak ditemukan", 404);
        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::Error($e->getMessage(), 500);
        }
    }
}
