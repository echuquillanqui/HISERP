<?php

namespace App\Exports;

use App\Models\InventoryMovement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SoldMedicinesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private array $filters = []
    ) {
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Código',
            'Unidades vendidas',
            'Total vendido',
            'Unidades devueltas',
            'Total devuelto',
            'Unidades netas',
            'Total neto',
        ];
    }

    public function collection(): Collection
    {
        return InventoryMovement::query()
            ->selectRaw("
                product_id,
                SUM(CASE WHEN movement_type = 'salida' THEN quantity ELSE 0 END) as sold_units,
                SUM(CASE WHEN movement_type = 'salida' THEN quantity * COALESCE(unit_price, 0) ELSE 0 END) as sold_total,
                SUM(CASE WHEN movement_type = 'entrada' THEN quantity ELSE 0 END) as returned_units,
                SUM(CASE WHEN movement_type = 'entrada' THEN quantity * COALESCE(unit_price, 0) ELSE 0 END) as returned_total,
                SUM(CASE WHEN movement_type = 'salida' THEN quantity ELSE -quantity END) as net_units,
                SUM(CASE WHEN movement_type = 'salida' THEN quantity * COALESCE(unit_price, 0) ELSE -(quantity * COALESCE(unit_price, 0)) END) as net_total
            " )
            ->with('product')
            ->where('source', 'orden')
            ->whereIn('movement_type', ['salida', 'entrada'])
            ->when(!empty($this->filters['product_id']), fn ($q) => $q->where('product_id', $this->filters['product_id']))
            ->when(!empty($this->filters['from_date']), fn ($q) => $q->whereDate('movement_at', '>=', $this->filters['from_date']))
            ->when(!empty($this->filters['to_date']), fn ($q) => $q->whereDate('movement_at', '<=', $this->filters['to_date']))
            ->groupBy('product_id')
            ->orderByDesc('net_units')
            ->get()
            ->map(function ($row) {
                return [
                    $row->product?->name ?? 'N/A',
                    $row->product?->code ?? '-',
                    (int) $row->sold_units,
                    (float) $row->sold_total,
                    (int) $row->returned_units,
                    (float) $row->returned_total,
                    (int) $row->net_units,
                    (float) $row->net_total,
                ];
            });
    }
}
