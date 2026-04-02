<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TomographyOperationalReportExport implements FromView
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
}
