<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Repositories\SettingRepository;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private $repo;

    public function __construct(SettingRepository $repo)
    {
        $this->repo = $repo;
        $this->middleware('auth:sanctum');
        $this->middleware('admin')->only(['update', 'store', 'destroy']);
    }

    /**
     * Get all settings
     */
    public function index()
    {
        try {
            $settings = $this->repo->getAll();
            return ApiResponse::Success($settings, 'Semua settings berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    /**
     * Get single setting by key
     */
    public function show($key)
    {
        try {
            $setting = $this->repo->getByKey($key);
            
            if (!$setting) {
                return ApiResponse::Notfound('Setting tidak ditemukan');
            }

            return ApiResponse::Success($setting, 'Setting berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    /**
     * Store new setting
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'key' => 'required|string|unique:settings,key',
                'value' => 'required',
                'description' => 'nullable|string',
            ]);

            $setting = $this->repo->create([
                'key' => $request->key,
                'value' => $request->value,
                'description' => $request->description,
            ]);

            return ApiResponse::Create('Setting berhasil ditambahkan', $setting);
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    /**
     * Update setting by key
     */
    public function update(Request $request, $key)
    {
        try {
            $request->validate([
                'value' => 'required',
                'description' => 'nullable|string',
            ]);

            $setting = $this->repo->update($key, [
                'value' => $request->value,
                'description' => $request->description ?? $setting->description ?? null,
            ]);

            if (!$setting) {
                return ApiResponse::Notfound('Setting tidak ditemukan');
            }

            return ApiResponse::Success($setting, 'Setting berhasil diperbarui');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    /**
     * Delete setting by key
     */
    public function destroy($key)
    {
        try {
            $deleted = $this->repo->delete($key);

            if (!$deleted) {
                return ApiResponse::Notfound('Setting tidak ditemukan');
            }

            return ApiResponse::Success(null, 'Setting berhasil dihapus');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    /**
     * Get all attendance-related settings
     */
    public function getAttendanceSettings()
    {
        try {
            $keys = [
                'school_latitude',
                'school_longitude',
                'attendance_radius',
                'attendance_start_time',
                'attendance_end_time',
                'on_time_until',
                'late_start_time',
            ];

            $settings = [];
            foreach ($keys as $key) {
                $settings[$key] = $this->repo->get($key);
            }

            return ApiResponse::Success($settings, 'Attendance settings berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults()
    {
        try {
            $this->repo->initializeDefaults();
            return ApiResponse::Success(null, 'Default settings berhasil diinisialisasi');
        } catch (\Exception $e) {
            return ApiResponse::Error($e->getMessage());
        }
    }
}
