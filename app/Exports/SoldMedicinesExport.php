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
        ];
    }

    public function collection(): Collection
    {
        return InventoryMovement::query()
            ->selectRaw('product_id, SUM(quantity) as sold_units, SUM(quantity * COALESCE(unit_price, 0)) as sold_total')
            ->with('product')
            ->where('source', 'orden')
            ->where('movement_type', 'salida')
            ->when(!empty($this->filters['product_id']), fn ($q) => $q->where('product_id', $this->filters['product_id']))
            ->when(!empty($this->filters['from_date']), fn ($q) => $q->whereDate('movement_at', '>=', $this->filters['from_date']))
            ->when(!empty($this->filters['to_date']), fn ($q) => $q->whereDate('movement_at', '<=', $this->filters['to_date']))
            ->groupBy('product_id')
            ->orderByDesc('sold_units')
            ->get()
            ->map(function ($row) {
                return [
                    $row->product?->name ?? 'N/A',
                    $row->product?->code ?? '-',
                    (int) $row->sold_units,
                    (float) $row->sold_total,
                ];
            });
    }
}
