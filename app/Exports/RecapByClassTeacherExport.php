<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class RecapByClassTeacherExport implements FromCollection, WithHeadings, WithStyles
{
    protected $teacherId;
    protected $params;
    protected $kelasId;

    public function __construct($teacherId, array $params, $kelasId)
    {
        $this->teacherId = $teacherId;
        $this->params = $params;
        $this->kelasId = $kelasId;
    }

    public function collection()
    {
        $students = Student::with(['user:id,name','kelas:id,nama'])
            ->where('kelas_id', $this->kelasId)
            ->get()
            ->map(function($student) {
                $attendanceCount = Attendance::where('student_id', $student->id);

                // Filter bulan dan tahun
                if(isset($this->params['month']) && !empty($this->params['month'])) {
                    $attendanceCount->whereMonth('created_at', $this->params['month']);
                }

                if(isset($this->params['year']) && !empty($this->params['year'])) {
                    $attendanceCount->whereYear('created_at', $this->params['year']);
                }

                return [
                    'siswa_id' => $student->id,
                    'name' => $student->user->name,
                    'nis' => $student->nis,
                    'attendance_count' => (int)(clone $attendanceCount)->count(),
                    'hadir' => (int)(clone $attendanceCount)->where('status', 'hadir')->count(),
                    'izin' => (int)(clone $attendanceCount)->where('status', 'izin')->count(),
                    'sakit' => (int)(clone $attendanceCount)->where('status', 'sakit')->count(),
                    'alpha' => (int)(clone $attendanceCount)->where('status', 'tidak hadir')->count(),
                    'libur' => (int)(clone $attendanceCount)->where('status', 'libur')->count(),
                    'terlambat' => (int)(clone $attendanceCount)->where('status', 'telat')->count(),
                ];
            });

        return $students;
    }

    public function headings(): array
    {
        return [
            'Siswa ID',
            'Nama Siswa',
            'NIS',
            'Total',
            '✓ Hadir',
            '📋 Izin',
            '🤒 Sakit',
            '✗ Alpha',
            '🏠 Libur',
            '⏰ Terlambat'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setAutoFilter('A1:J1');
        
        // Style header row
        $headerStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4788'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'border' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        
        // Style data rows
        $dataStyle = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'border' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];
        
        // Apply header style
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        
        // Apply data style and row colors
        $rowCount = $sheet->getHighestRow();
        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getStyle("A{$i}:J{$i}")->applyFromArray($dataStyle);
            
            // Apply number format untuk kolom angka (D-J)
            $sheet->getStyle("D{$i}:J{$i}")->getNumberFormat()->setFormatCode('0');
            
            // Alternate row colors
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:J{$i}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color('F5F5F5'));
            }
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(12);
        
        // Set header row height
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        return [];
    }
}
