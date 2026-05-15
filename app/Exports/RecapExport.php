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

class RecapExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }
    public function collection()
    {
        $params = $this->params;
        
        $students = Student::with(['user:id,name','kelas:id,nama'])
            ->get()
            ->map(function($student) use ($params) {
                $attendanceCount = Attendance::where('student_id', $student->id);

                //filter bulan dan tahun
                if(isset($params['month']) && !empty($params['month']) && 
                    isset($params['year']) && !empty($params['year'])) {
                        $attendanceCount->whereMonth('created_at', $params['month'])
                            ->whereYear('created_at', $params['year']);
                } elseif ( isset($params['month']) && !empty($params['month'])) {
                    $attendanceCount->whereMonth('created_at', $params['month']);
                } elseif ( isset($params['year']) && !empty($params['year'])) {
                    $attendanceCount->whereYear('created_at', $params['year']);
                }

                //filter kelas
                if(isset($params['kelas_id']) && !empty($params['kelas_id'])) {
                    $attendanceCount->whereHas('student', function($q) use ($params) {
                        $q->where('kelas_id', $params['kelas_id']);
                    });
                }
        
                //filter status
                if(isset($params['status']) && !empty($params['status'])) {
                    $attendanceCount->where('status', $params['status']);
                }
                
                return [
                    'siswa_id' => $student->id,
                    'name' => $student->user->name,
                    'nis' => $student->nis,
                    'class' => $student->kelas->nama,
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
            'Kelas',
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
        $sheet->setAutoFilter('A1:K1');
        
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
        
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
        
        $rowCount = $sheet->getHighestRow();
        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getStyle("A{$i}:K{$i}")->applyFromArray($dataStyle);
            
            // Apply number format untuk kolom angka (E-K)
            $sheet->getStyle("E{$i}:K{$i}")->getNumberFormat()->setFormatCode('0');
            
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:K{$i}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color('F5F5F5')); 
            }
        }
        
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(10);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(12);

        $sheet->getRowDimension(1)->setRowHeight(25);
        
        return [];
    }
}
