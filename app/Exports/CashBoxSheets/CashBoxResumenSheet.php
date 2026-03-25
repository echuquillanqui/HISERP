<?php

namespace App\Exports\CashBoxSheets;

use App\Models\Expense;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CashBoxResumenSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    public function __construct(
        private Carbon $startDate,
        private Carbon $endDate,
        private string $rangeLabel
    ) {
    }

    public function headings(): array
    {
        return [
            'CONCEPTO',
            'MONTO',
            'PERIODO',
        ];
    }

    public function collection(): Collection
    {
        $totalIngresos = Order::query()
            ->whereBetween('created_at', [$this->startDate->copy()->startOfDay(), $this->endDate->copy()->endOfDay()])
            ->sum('total');

        $totalEgresos = Expense::query()
            ->whereBetween('created_at', [$this->startDate->copy()->startOfDay(), $this->endDate->copy()->endOfDay()])
            ->sum('amount');

        return collect([
            [
                'TOTAL INGRESOS',
                (float) $totalIngresos,
                sprintf('%s (%s a %s)', $this->rangeLabel, $this->startDate->toDateString(), $this->endDate->toDateString()),
            ],
            [
                'TOTAL EGRESOS',
                (float) $totalEgresos,
                sprintf('%s (%s a %s)', $this->rangeLabel, $this->startDate->toDateString(), $this->endDate->toDateString()),
            ],
            [
                'SALDO NETO',
                (float) ($totalIngresos - $totalEgresos),
                sprintf('%s (%s a %s)', $this->rangeLabel, $this->startDate->toDateString(), $this->endDate->toDateString()),
            ],
        ]);
    }

    public function title(): string
    {
        return '0. Resumen';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $highestColumn = $event->sheet->getDelegate()->getHighestColumn();
                $highestRow = $event->sheet->getDelegate()->getHighestRow();

                $event->sheet->getDelegate()->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '212529'],
                    ],
                ]);

                $event->sheet->getDelegate()->getStyle('A2:A' . $highestRow)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('B2:B' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}
