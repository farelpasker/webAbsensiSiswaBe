<?php

namespace App\Services;

use App\Repositories\AttendanceRepository;
use App\Repositories\HolidayRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AttendanceService
{
    private $holidayrepo;
    private $attendancerepo;
    private $whatsappService;
    private $studentService;

    public function __construct(AttendanceRepository $attendancerepo, HolidayRepository $holidayrepo, WhatsAppService $whatsappService, StudentService $studentService)
    {
        $this->attendancerepo = $attendancerepo;
        $this->holidayrepo = $holidayrepo;
        $this->whatsappService = $whatsappService;
        $this->studentService = $studentService;
    }

    public function recordAttendance($student, $latitude, $longitude, $faceDescriptor)
    {
        $date = now()->toDateString();
        
        $siap = $this->attendancerepo->isAttended($student->id, $date);

        if($siap) {
            throw new \Exception('Anda sudah melakukan absensi hari ini');
        }

        if (!$student->face_descriptor) {
            throw new \Exception('Anda belum melakukan registrasi wajah');
        }

        $faceVerification = $this->studentService->verifyFaceDescriptor($student, $faceDescriptor);
        if (!$faceVerification['match']) {
            throw new \Exception('Wajah tidak cocok. Jarak: ' . $faceVerification['distance']);
        }

        $time = now()->toTimeString();

        if ($time < '06:00:00' || $time > '15:00:00') {
            throw new \Exception('Waktu absensi di luar jam sekolah');
        }

        //gps sekolah
        $schoolLatitude = -8.226982;
        $schoolLongitude = 113.543931;

        $distance = $this->calculateDistance($latitude, $longitude, $schoolLatitude, $schoolLongitude);

        if($distance > 50) {
            throw new \Exception('Anda berada di luar radius sekolah. jarak: ' . round($distance, 2) . ' meter');
        }

        $carbon = Carbon::parse($date);
        if(
            $this->holidayrepo->isHoliday($date) || $carbon->isSaturday() || $carbon->isSunday() || $this->isNationalHoliday($date)) {
            throw new \Exception('Hari ini adalah hari libur, tidak perlu melakukan absensi');
        } else if ($time <= '08:00:00') {
            $status = 'hadir';
        } else {
            $status = 'telat';
        }

        $attendance = $this->attendancerepo->create([
            'student_id' => $student->id,
            'date' => $date,
            'time_in' => $time,
            'status' => $status,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        $parent = $student->parent;
        $message = "Halo {$parent->name},
        
        Kami informasikan bahwa anak Anda {$student->user->name} telah melakukan absensi sekolah.
        
        📅 Tanggal: {$date}
        ⏰ Jam: {$time}
        📌 Status: {$status}

        Terima kasih.";
    
        try {
            $this->whatsappService->sendMessage($parent->phone, $message);
        } catch (\Exception $e) {
            \Log::error('Gagal mengirim pesan WhatsApp: ' . $e->getMessage());
        }

        return $attendance;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; 

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function isNationalHoliday($date)
    {
        $response = Http::get(
            'https://dayoffapi.vercel.app/api'
        );

        $holidays = $response->json();

        foreach ($holidays as $holiday) {

            if ($holiday['tanggal'] == $date) {
                return true;
            }
        }

        return false;
    }
}