<?php

namespace App\Exports\CashBoxSheets;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CashBoxDetallesSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
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
            'ORDEN',
            'PACIENTE',
            'DNI',
            'EXÁMENES',
            'CANT. EXÁMENES',
            'TOTAL ORDEN',
            'PERIODO',
        ];
    }

    public function collection(): Collection
    {
        return Order::with(['patient', 'details'])
            ->whereBetween('created_at', [$this->startDate->copy()->startOfDay(), $this->endDate->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get()
            ->map(function (Order $order) {
                $examNames = $order->details
                    ->map(fn ($detail) => trim((string) $detail->name))
                    ->filter()
                    ->values();

                return [
                    $order->created_at?->format('d/m/Y H:i'),
                    $order->code,
                    trim(($order->patient->last_name ?? '') . ' ' . ($order->patient->first_name ?? '')),
                    $order->patient->dni ?? '-',
                    $examNames->isNotEmpty() ? $examNames->implode(' | ') : 'SIN EXÁMENES REGISTRADOS',
                    $examNames->count(),
                    (float) $order->total,
                    sprintf('%s (%s a %s)', $this->rangeLabel, $this->startDate->toDateString(), $this->endDate->toDateString()),
                ];
            });
    }

    public function title(): string
    {
        return '3. Detalle Orden';
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
                        'startColor' => ['rgb' => '6F42C1'],
                    ],
                ]);

                $event->sheet->getDelegate()->setAutoFilter('A1:' . $highestColumn . $highestRow);
                $event->sheet->freezePane('A2');
                $event->sheet->getDelegate()->getStyle('G2:G' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                $event->sheet->getDelegate()->getStyle('E2:E' . $highestRow)
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP);
            },
        ];
    }
}
