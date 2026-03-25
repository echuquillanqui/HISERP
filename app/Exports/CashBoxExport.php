<?php

namespace App\Exports;

use App\Exports\CashBoxSheets\CashBoxDetallesSheet;
use App\Exports\CashBoxSheets\CashBoxEgresosSheet;
use App\Exports\CashBoxSheets\CashBoxIngresosSheet;
use App\Exports\CashBoxSheets\CashBoxResumenSheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CashBoxExport implements WithMultipleSheets
{
    public function __construct(
        private Carbon $startDate,
        private Carbon $endDate,
        private string $rangeLabel
    ) {
    }

    public function sheets(): array
    {
        return [
            new CashBoxResumenSheet($this->startDate, $this->endDate, $this->rangeLabel),
            new CashBoxIngresosSheet($this->startDate, $this->endDate, $this->rangeLabel),
            new CashBoxEgresosSheet($this->startDate, $this->endDate, $this->rangeLabel),
            new CashBoxDetallesSheet($this->startDate, $this->endDate, $this->rangeLabel),
        ];
    }
}
