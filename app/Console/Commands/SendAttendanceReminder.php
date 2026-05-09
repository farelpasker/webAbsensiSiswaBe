<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\WhatsAppService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-attendance-reminder')]
#[Description('Kirim notifikasi WA ke siswa & ortu 5 menit sebelum telat (07:55)')]
class SendAttendanceReminder extends Command
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    public function handle()
    {
        // Cari semua siswa yang belum absen hari ini
        $studentsWithoutAttendance = Student::whereDoesntHave('attendances', function($query) {
            $query->where('date', now()->toDateString());
        })
        ->with(['user', 'parent'])
        ->get();

        if ($studentsWithoutAttendance->isEmpty()) {
            $this->info('✓ Semua siswa sudah absen');
            return;
        }

        $this->info("Mengirim notifikasi ke {$studentsWithoutAttendance->count()} siswa...\n");

        foreach ($studentsWithoutAttendance as $student) {
            // Kirim ke siswa
            if ($student->user->phone) {
                $msgSiswa = "Halo {$student->user->name}, kamu punya 5 menit lagi untuk absen sebelum dianggap telat! ⏰ Segera lakukan absen. 📱";
                $this->sendMessage($student->user->phone, $msgSiswa, $student->user->name, 'Siswa');
            }

            // Kirim ke ortu
            if ($student->parent && $student->parent->phone) {
                $msgOrtu = "Halo {$student->parent->name}, anak Anda {$student->user->name} belum absen. Waktu absen tutup dalam 5 menit, segera suruh absen atau dianggap telat! 👨‍👩‍👧";
                $this->sendMessage($student->parent->phone, $msgOrtu, $student->parent->name, 'Ortu');
            }
        }

        $this->info("\n✓ Selesai");
    }

    private function sendMessage($phone, $message, $name, $type)
    {
        try {
            $response = $this->whatsappService->sendMessage($phone, $message);
            
            if (isset($response['status']) && $response['status'] === true) {
                $this->line("  ✓ {$type} ({$name})");
            } else {
                $this->warn("  ✗ {$type} ({$name}) - gagal");
            }
        } catch (\Exception $e) {
            $this->error("  ✗ {$type} ({$name}) - {$e->getMessage()}");
        }
    }
}

