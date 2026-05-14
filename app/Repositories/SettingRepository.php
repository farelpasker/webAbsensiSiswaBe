<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository
{
    private $model;

    public function __construct(Setting $model)
    {
        $this->model = $model;
    }

    public function get($key, $default = null)
    {
        return Setting::get($key, $default);
    }

    public function set($key, $value, $type = 'string')
    {
        return Setting::set($key, $value, $type);
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getByKey($key)
    {
        return $this->model->where('key', $key)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($key, array $data)
    {
        $setting = $this->model->where('key', $key)->first();
        if ($setting) {
            $setting->update($data);
        }
        return $setting;
    }

    public function delete($key)
    {
        $setting = $this->model->where('key', $key)->first();
        if ($setting) {
            $setting->delete();
            return true;
        }
        return false;
    }

    /**
     * Default settings untuk attendance
     */
    public function initializeDefaults()
    {
        $defaults = [
            'school_latitude' => -8.226982,
            'school_longitude' => 113.543931,
            'attendance_radius' => 50, // meters
            'attendance_start_time' => '06:00:00',
            'attendance_end_time' => '15:00:00',
            'on_time_until' => '08:00:00',
            'late_start_time' => '08:00:01',
        ];

        foreach ($defaults as $key => $value) {
            if (!$this->getByKey($key)) {
                $this->create([
                    'key' => $key,
                    'value' => $value,
                    'description' => $this->getDescription($key),
                ]);
            }
        }
    }

    private function getDescription($key)
    {
        $descriptions = [
            'school_latitude' => 'Latitude lokasi sekolah',
            'school_longitude' => 'Longitude lokasi sekolah',
            'attendance_radius' => 'Radius area sekolah dalam meter',
            'attendance_start_time' => 'Jam mulai absensi (HH:MM:SS)',
            'attendance_end_time' => 'Jam akhir absensi (HH:MM:SS)',
            'on_time_until' => 'Batas waktu untuk status "hadir" (HH:MM:SS)',
            'late_start_time' => 'Jam mulai dianggap "telat" (HH:MM:SS)',
        ];

        return $descriptions[$key] ?? null;
    }
}
