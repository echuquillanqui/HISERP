<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TomographyOperationalReportExport implements FromView, WithEvents
{
    public function __construct(
        private array $rows,
        private Carbon $startDate,
        private Carbon $endDate,
        private string $rangeLabel,
    ) {
    }

    public function view(): View
    {
        return view('radiology.control_insumos.report_excel', [
            'rows' => $this->rows,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'rangeLabel' => $this->rangeLabel,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(2, count($this->rows) + 2);
                $tableRange = "A2:W{$lastRow}";

                $sheet->freezePane('A3');
                $sheet->setAutoFilter($tableRange);

                $sheet->getStyle('A1:W1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FF0D6EFD'],
                    ],
                ]);

                $sheet->getStyle('A2:W2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF0F2F57']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFEAF3FF'],
                    ],
                ]);

                $sheet->getStyle($tableRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFBED8F7'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ]);

                foreach (['A', 'B', 'D', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'T', 'U'] as $column) {
                    $sheet->getStyle("{$column}2:{$column}{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
