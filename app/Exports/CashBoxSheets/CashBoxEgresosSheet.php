<?php

namespace App\Exports\CashBoxSheets;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CashBoxEgresosSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
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
            'FECHA',
            'DESCRIPCIÓN',
            'TIPO COMPROBANTE',
            'MONTO',
            'PERIODO',
        ];
    }

    public function collection(): Collection
    {
        return Expense::query()
            ->whereBetween('created_at', [$this->startDate->copy()->startOfDay(), $this->endDate->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get()
            ->map(function (Expense $expense) {
                return [
                    $expense->created_at?->format('d/m/Y H:i'),
                    $expense->description,
                    strtoupper((string) $expense->voucher_type),
                    (float) $expense->amount,
                    sprintf('%s (%s a %s)', $this->rangeLabel, $this->startDate->toDateString(), $this->endDate->toDateString()),
                ];
            });
    }

    public function title(): string
    {
        return '2. Egresos';
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
                        'startColor' => ['rgb' => '198754'],
                    ],
                ]);

                $event->sheet->getDelegate()->setAutoFilter('A1:' . $highestColumn . $highestRow);
                $event->sheet->freezePane('A2');
                $event->sheet->getDelegate()->getStyle('D2:D' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}
