<?php

namespace App\Exports;

use App\Models\InventoryMovement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryMovementsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private array $filters = []
    ) {
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Producto',
            'Código',
            'Tipo',
            'Cantidad',
            'Stock Antes',
            'Stock Después',
            'Origen',
            'Orden',
            'Paciente',
            'Costo Unitario',
            'Precio Unitario',
            'Observación',
        ];
    }

    public function collection(): Collection
    {
        return InventoryMovement::query()
            ->with(['product', 'order.patient'])
            ->when(!empty($this->filters['product_id']), fn ($q) => $q->where('product_id', $this->filters['product_id']))
            ->when(!empty($this->filters['from_date']), fn ($q) => $q->whereDate('movement_at', '>=', $this->filters['from_date']))
            ->when(!empty($this->filters['to_date']), fn ($q) => $q->whereDate('movement_at', '<=', $this->filters['to_date']))
            ->orderByDesc('movement_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (InventoryMovement $movement) {
                $patientName = trim(($movement->order?->patient?->first_name ?? '') . ' ' . ($movement->order?->patient?->last_name ?? ''));

                return [
                    $movement->movement_at?->format('d/m/Y H:i'),
                    $movement->product?->name,
                    $movement->product?->code,
                    strtoupper($movement->movement_type),
                    (int) $movement->quantity,
                    (int) $movement->stock_before,
                    (int) $movement->stock_after,
                    strtoupper($movement->source),
                    $movement->order?->code ?? ($movement->order_id ? '#' . $movement->order_id : '-'),
                    $patientName !== '' ? $patientName : '-',
                    $movement->unit_cost,
                    $movement->unit_price,
                    $movement->notes,
                ];
            });
    }
}
