<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Attendance::query()
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('kelas', 'students.kelas_id', '=', 'kelas.id')
            ->select(
                'users.name as student_name',
                'students.nis',
                'kelas.nama as class_name',
                'attendances.date',
                'attendances.time_in',
                'attendances.status'
            );

            //filter bulan dan tahun
            if(isset($this->params['month']) && !empty($this->params['month']) && 
                isset($this->params['year']) && !empty($this->params['year'])) {
                    $query->whereMonth('attendances.created_at', $this->params['month'])
                        ->whereYear('attendances.created_at', $this->params['year']);
            } elseif ( isset($this->params['month']) && !empty($this->params['month'])) {
                $query->whereMonth('attendances.created_at', $this->params['month']);
            } elseif ( isset($this->params['year']) && !empty($this->params['year'])) {
                $query->whereYear('attendances.created_at', $this->params['year']);
            }

            //filter kelas
            if(isset($this->params['kelas_id']) && !empty($this->params['kelas_id'])) {
                $query->where('students.kelas_id', $this->params['kelas_id']);
            }
    
            //filter status
            if(isset($this->params['status']) && !empty($this->params['status'])) {
                $query->where('attendances.status', $this->params['status']);
            } elseif(isset($this->params['status']) && $this->params['status'] === 'libur') {
                $query->where('attendances.status', 'libur');
            }
        return $query->orderBy('attendances.date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'NIS',
            'Class Name',
            'Date',
            'Time In',
            'Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '366092'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Apply borders to all data cells
        $sheet->getStyle('A2:F' . ($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center align Status column
        $sheet->getStyle('F2:F' . ($sheet->getHighestRow()))->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Color status cells
        for ($i = 2; $i <= $sheet->getHighestRow(); $i++) {
            $status = $sheet->getCell("F{$i}")->getValue();
            $color = match($status) {
                'hadir' => 'C6EFCE',      // Green
                'telat' => 'FFEB9C',      // Yellow
                'tidak hadir' => 'FFC7CE', // Red
                'libur' => 'E2EFDA',      // Light Green
                default => 'FFFFFF',      // White
            };

            $sheet->getStyle("F{$i}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $color],
                ],
            ]);
        }

        return [];
    }
}
