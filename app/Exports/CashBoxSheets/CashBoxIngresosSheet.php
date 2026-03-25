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
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CashBoxIngresosSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
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
            'TOTAL',
            'ESTADO',
            'PERIODO',
        ];
    }

    public function collection(): Collection
    {
        $rows = Order::with(['patient'])
            ->whereBetween('created_at', [$this->startDate->copy()->startOfDay(), $this->endDate->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get()
            ->map(function (Order $order) {
                return [
                    $order->created_at?->format('d/m/Y H:i'),
                    $order->code,
                    trim(($order->patient->last_name ?? '') . ' ' . ($order->patient->first_name ?? '')),
                    $order->patient->dni ?? '-',
                    (float) $order->total,
                    strtoupper((string) ($order->payment_status ?? 'pendiente')),
                    sprintf('%s (%s a %s)', $this->rangeLabel, $this->startDate->toDateString(), $this->endDate->toDateString()),
                ];
            });

        $rows->push([null, null, null, 'TOTAL INGRESOS', (float) $rows->sum(4), null, null]);

        return $rows;
    }

    public function title(): string
    {
        return '1. Ingresos';
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
                        'startColor' => ['rgb' => '0D6EFD'],
                    ],
                ]);

                $event->sheet->getDelegate()->setAutoFilter('A1:' . $highestColumn . $highestRow);
                $event->sheet->freezePane('A2');
                $event->sheet->getDelegate()->getStyle('E2:E' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $event->sheet->getDelegate()->getStyle('A' . $highestRow . ':E' . $highestRow)->getFont()->setBold(true);
            },
        ];
    }
}
