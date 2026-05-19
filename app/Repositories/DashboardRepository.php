<?php

namespace App\Repositories;

use App\Models\Student;
use Illuminate\Support\Carbon;

class DashboardRepository
{
    public function adminDashboard($year)
    {
        $student = Student::count();

        $studentCount = $student;
        $siswaHadir = Student::whereHas('attendances', function ($query) {
            $query->whereDate('created_at', now()->toDateString())
                  ->whereIn('status', ['hadir','telat']);
        })->count();
        $siswaTelat = Student::whereHas('attendances', function ($query) {
            $query->whereDate('created_at', now()->toDateString())
                  ->where('status', 'telat');
        })->count();
        $siswaIzinSakit = Student::whereHas('attendances', function ($query) {
            $query->whereDate('created_at', now()->toDateString())
                  ->whereIn('status', ['izin','sakit']);
        })->count();

        $monthlyStats = [];

        for($month = 1; $month <= 12; $month++) {
            $total = Student::whereHas('attendances', function ($query) use ($month, $year) {
                $query->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                      ->whereIn('status', ['hadir','telat']);
            })->count();

            $monthlyStats[] = [
                'month' => Carbon::create()
                    ->month($month)
                    ->translatedFormat('M'),
                'total' => $total
            ];
        }
        return [
            'studentCount' => $studentCount,
            'siswaHadir' => $siswaHadir,
            'siswaTelat' => $siswaTelat,
            'siswaIzinSakit' => $siswaIzinSakit,
            'monthlyStats' => $monthlyStats
        ];
    }

    public function getAttendanceSummary()
    {
        // Total siswa
        $totalSiswa = Student::count();

        // Hitung total RECORD attendance keseluruhan berdasarkan status
        $siswaHadir = \App\Models\Attendance::whereIn('status', ['hadir', 'telat'])->count();
        $siswaTelat = \App\Models\Attendance::where('status', 'telat')->count();
        $siswaIzinSakit = \App\Models\Attendance::whereIn('status', ['izin', 'sakit'])->count();
        $siswaTidakHadir = \App\Models\Attendance::where('status', 'tidak hadir')->count();

        // Total record yang ter-absensi keseluruhan
        $totalTerabsensi = $siswaHadir + $siswaTelat + $siswaIzinSakit + $siswaTidakHadir;

        // Rasio kehadiran = (hadir + terlambat) / total siswa
        $siswaHadirDanTelat = $siswaHadir + $siswaTelat;
        $rasioKehadiran = $totalSiswa > 0 ? round(($siswaHadirDanTelat / $totalSiswa) * 100, 1) : 0;

        return [
            'total_hadir' => $siswaHadir,
            'total_terlambat' => $siswaTelat,
            'total_izin_sakit' => $siswaIzinSakit,
            'total_tidak_hadir' => $siswaTidakHadir,
            'total_terabsensi' => $totalTerabsensi,
            'total_siswa' => $totalSiswa,
            'rasio_kehadiran' => $rasioKehadiran . '%'
        ];
    }
}