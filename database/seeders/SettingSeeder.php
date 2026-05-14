<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // School Location
        Setting::set('school_latitude', -8.226982, 'decimal');
        Setting::set('school_longitude', 113.543931, 'decimal');

        // Attendance Settings
        Setting::set('attendance_radius', 50, 'integer'); // dalam meter
        Setting::set('attendance_start', '06:00:00', 'string'); // jam mulai absensi
        Setting::set('attendance_telat', '08:00:00', 'string'); // jam mulai dihitung telat
        Setting::set('attendance_end', '15:00:00', 'string'); // jam akhir absensi

        // System Settings
        Setting::set('school_name', 'SMA Al-Hana', 'string');
        Setting::set('school_email', 'admin@smalhana.sch.id', 'string');
        Setting::set('school_phone', '08123456789', 'string');
    }
}
