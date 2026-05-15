<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class StudentsExport implements FromCollection, WithHeadings, WithStyles
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
        $query = Student::with(['user:id,name,email','kelas:id,nama','parent:id,name,email,phone'])
            ->select('id', 'nis', 'user_id', 'kelas_id', 'parent_id');
        
        if(isset($params['search']) && !empty($params['search'])) {
            $query->whereHas('user', function($q) use ($params) {
                $q->where('name', 'like', '%' . $params['search'] . '%');
            });
        }

        if(isset($params['kelas_id']) && !empty($params['kelas_id'])) {
            $query->where('kelas_id', $params['kelas_id']);
        }

        return $query->get()->map(function($student) {
            return [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->user->name,
                'class' => $student->kelas->nama,
                'parent_name' => $student->parent ? $student->parent->name : '-',
                'parent_phone' => $student->parent ? $student->parent->phone : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Nama Orang Tua',
            'Telepon Orang Tua',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setAutoFilter('A1:F1');
        
        // Style header row
        $headerStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4788'], // Dark blue
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // White text
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
                'horizontal' => Alignment::HORIZONTAL_LEFT,
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
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Apply data style and row colors
        $rowCount = $sheet->getHighestRow();
        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getStyle("A{$i}:F{$i}")->applyFromArray($dataStyle);
            
            // Alternate row colors
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:F{$i}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color('F5F5F5')); // Light gray
            }
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(18);
        
        // Set header row height
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        return [];
    }
}
