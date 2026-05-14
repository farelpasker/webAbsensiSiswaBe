<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate {--date= : Tanggal untuk generate attendance (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate attendance records untuk semua siswa dengan status default "tidak hadir"';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $date = $this->option('date') ?? now()->toDateString();

            // Validasi format tanggal
            if (!$this->isValidDate($date)) {
                $this->error('Format tanggal tidak valid. Gunakan format Y-m-d');
                return self::FAILURE;
            }

            $this->info("Generating attendance records untuk tanggal: {$date}");

            $students = Student::all();
            $created = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($students as $student) {
                $exists = Attendance::where('student_id', $student->id)
                    ->where('date', $date)
                    ->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    Attendance::create([
                        'student_id' => $student->id,
                        'date' => $date,
                        'time_in' => '00:00:00',
                        'time_out' => null,
                        'status' => 'tidak hadir',
                    ]);
                    $created++;
                }
            }

            DB::commit();

            $this->info("✓ Selesai!");
            $this->info("  - Created: {$created} records");
            $this->info("  - Skipped: {$skipped} records (sudah ada)");

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Validasi format tanggal
     */
    private function isValidDate($date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
