<?php

namespace App\Http\Controllers;

use App\Exports\TomographyOperationalReportExport;
use App\Models\IopamidolBrand;
use App\Models\OrderTomography;
use App\Models\TomographyResult;
use App\Models\TomographySupplyControl;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ControlInsumoController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveDateRange($request);

        $resultsQuery = TomographyResult::query()
            ->when($filters['startDate'], fn ($query) => $query->whereDate('result_date', '>=', $filters['startDate']->toDateString()))
            ->when($filters['endDate'], fn ($query) => $query->whereDate('result_date', '<=', $filters['endDate']->toDateString()));

        $entriesQuery = TomographySupplyControl::query()
            ->when($filters['startDate'], fn ($query) => $query->whereDate('created_at', '>=', $filters['startDate']->toDateString()))
            ->when($filters['endDate'], fn ($query) => $query->whereDate('created_at', '<=', $filters['endDate']->toDateString()));

        $platesIn = (int) (clone $entriesQuery)->sum('plates_in');
        $iopamidolIn = (float) (clone $entriesQuery)->sum('iopamidol_in');

        $platesOutResults = (int) (clone $resultsQuery)->sum('plates_used');
        $iopamidolOutResults = (float) (clone $resultsQuery)->sum('iopamidol_used');

        $platesOpeningBalance = (int) TomographySupplyControl::query()
            ->whereDate('created_at', '<', $filters['startDate']->toDateString())
            ->sum('plates_in')
            - (int) TomographyResult::query()
                ->whereDate('result_date', '<', $filters['startDate']->toDateString())
                ->sum('plates_used');

        $iopamidolOpeningBalance = (float) TomographySupplyControl::query()
            ->whereDate('created_at', '<', $filters['startDate']->toDateString())
            ->sum('iopamidol_in')
            - (float) TomographyResult::query()
                ->whereDate('result_date', '<', $filters['startDate']->toDateString())
                ->sum('iopamidol_used');

        $summary = [
            'plates_in' => $platesIn,
            'plates_out' => $platesOutResults,
            'plates_balance' => $platesOpeningBalance + $platesIn - $platesOutResults,
            'iopamidol_in' => round($iopamidolIn, 2),
            'iopamidol_out' => round($iopamidolOutResults, 2),
            'iopamidol_balance' => round($iopamidolOpeningBalance + $iopamidolIn - $iopamidolOutResults, 2),
            'orders_count' => OrderTomography::query()
                ->whereBetween('created_at', [$filters['startDate']->copy()->startOfDay(), $filters['endDate']->copy()->endOfDay()])
                ->count(),
            'results_count' => (clone $resultsQuery)->count(),
        ];

        $entries = TomographySupplyControl::query()
            ->with('iopamidolBrand:id,name')
            ->when($filters['startDate'], fn ($query) => $query->whereDate('created_at', '>=', $filters['startDate']->toDateString()))
            ->when($filters['endDate'], fn ($query) => $query->whereDate('created_at', '<=', $filters['endDate']->toDateString()))
            ->latest('id')
            ->limit(50)
            ->get();

        $platesOutputs = TomographyResult::query()
            ->with(['patient:id,first_name,last_name', 'orderTomography:id,code'])
            ->when($filters['startDate'], fn ($query) => $query->whereDate('result_date', '>=', $filters['startDate']->toDateString()))
            ->when($filters['endDate'], fn ($query) => $query->whereDate('result_date', '<=', $filters['endDate']->toDateString()))
            ->where('plates_used', '>', 0)
            ->orderByDesc('result_date')
            ->limit(50)
            ->get();

        $iopamidolOutputs = TomographyResult::query()
            ->with(['patient:id,first_name,last_name', 'orderTomography:id,code'])
            ->when($filters['startDate'], fn ($query) => $query->whereDate('result_date', '>=', $filters['startDate']->toDateString()))
            ->when($filters['endDate'], fn ($query) => $query->whereDate('result_date', '<=', $filters['endDate']->toDateString()))
            ->where('iopamidol_used', '>', 0)
            ->orderByDesc('result_date')
            ->limit(50)
            ->get();

        return view('radiology.control_insumos.index', [
            'summary' => $summary,
            'entries' => $entries,
            'platesOutputs' => $platesOutputs,
            'iopamidolOutputs' => $iopamidolOutputs,
            'iopamidolBrands' => IopamidolBrand::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'from' => $filters['startDate']->toDateString(),
                'to' => $filters['endDate']->toDateString(),
                'period' => $filters['period'],
                'date' => $filters['startDate']->toDateString(),
                'start_date' => $filters['startDate']->toDateString(),
                'end_date' => $filters['endDate']->toDateString(),
                'range_label' => $filters['label'],
            ],
        ]);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->resolveDateRange($request);
        $rows = $this->buildOperationalReportRows($filters['startDate'], $filters['endDate']);

        $pdf = Pdf::loadView('radiology.control_insumos.report_pdf', [
            'rows' => $rows,
            'rangeLabel' => $filters['label'],
            'startDate' => $filters['startDate'],
            'endDate' => $filters['endDate'],
        ])->setPaper('a3', 'landscape');

        return $pdf->download(sprintf(
            'reporte_tomografia_%s_a_%s.pdf',
            $filters['startDate']->toDateString(),
            $filters['endDate']->toDateString()
        ));
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->resolveDateRange($request);
        $rows = $this->buildOperationalReportRows($filters['startDate'], $filters['endDate']);

        return Excel::download(
            new TomographyOperationalReportExport($rows, $filters['startDate'], $filters['endDate'], $filters['label']),
            sprintf('reporte_tomografia_%s_a_%s.xlsx', $filters['startDate']->toDateString(), $filters['endDate']->toDateString())
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plates_in' => ['nullable', 'integer', 'min:0'],
            'iopamidol_brand_id' => ['nullable', 'exists:iopamidol_brands,id'],
            'iopamidol_presentation_ml' => ['nullable', 'integer', 'in:50,100'],
            'iopamidol_units' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $platesIn = (int) ($data['plates_in'] ?? 0);
        $iopamidolPresentation = isset($data['iopamidol_presentation_ml']) ? (int) $data['iopamidol_presentation_ml'] : null;
        $iopamidolUnits = $iopamidolPresentation ? (int) ($data['iopamidol_units'] ?? 1) : 0;
        $iopamidolIn = (float) (($iopamidolPresentation && $iopamidolUnits > 0) ? $iopamidolPresentation * $iopamidolUnits : 0);

        if ($iopamidolPresentation && empty($data['iopamidol_brand_id'])) {
            return back()->withErrors(['iopamidol_brand_id' => 'Seleccione una marca para el iopamidol.'])->withInput();
        }

        if ($platesIn === 0 && $iopamidolIn === 0.0) {
            return back()->withErrors(['plates_in' => 'Debe registrar al menos una entrada de placas o iopamidol.'])->withInput();
        }

        $currentPlatesBalance = (int) TomographySupplyControl::sum('plates_in') - (int) TomographyResult::sum('plates_used');

        $currentIopamidolBalance = (float) TomographySupplyControl::sum('iopamidol_in') - (float) TomographyResult::sum('iopamidol_used');

        TomographySupplyControl::create([
            'plates_in' => $platesIn,
            'iopamidol_in' => $iopamidolIn,
            'iopamidol_brand_id' => $iopamidolPresentation ? $data['iopamidol_brand_id'] : null,
            'iopamidol_presentation_ml' => $iopamidolPresentation,
            'iopamidol_units' => $iopamidolUnits,
            'plates_balance' => $currentPlatesBalance + $platesIn,
            'iopamidol_balance' => round($currentIopamidolBalance + $iopamidolIn, 2),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('control-insumos.index')->with('success', 'Entrada registrada correctamente.');
    }

    private function resolveDateRange(Request $request): array
    {
        $period = $request->get('period', 'daily');
        $today = Carbon::today();

        switch ($period) {
            case 'weekly':
                $startDate = $today->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $today->copy()->endOfWeek(Carbon::SUNDAY);
                $label = 'Semanal';
                break;
            case 'biweekly':
                if ($today->day <= 15) {
                    $startDate = $today->copy()->startOfMonth();
                    $endDate = $today->copy()->startOfMonth()->addDays(14);
                } else {
                    $startDate = $today->copy()->startOfMonth()->addDays(15);
                    $endDate = $today->copy()->endOfMonth();
                }
                $label = 'Quincenal';
                break;
            case 'monthly':
                $startDate = $today->copy()->startOfMonth();
                $endDate = $today->copy()->endOfMonth();
                $label = 'Mensual';
                break;
            case 'range':
                $startDate = $request->filled('start_date') ? Carbon::parse($request->string('start_date')) : $today->copy();
                $endDate = $request->filled('end_date') ? Carbon::parse($request->string('end_date')) : $startDate->copy();
                if ($endDate->lt($startDate)) {
                    [$startDate, $endDate] = [$endDate, $startDate];
                }
                $label = 'Rango Personalizado';
                break;
            case 'daily':
            default:
                $day = $request->get('date', $request->get('from', $today->toDateString()));
                $startDate = Carbon::parse($day);
                $endDate = Carbon::parse($day);
                $label = 'Diario';
                $period = 'daily';
                break;
        }

        return compact('period', 'startDate', 'endDate', 'label');
    }

    private function buildOperationalReportRows(Carbon $startDate, Carbon $endDate): array
    {
        $orders = OrderTomography::query()
            ->with([
                'patient:id,dni,first_name,last_name',
                'agreement:id,description',
                'items.radiography:id,description',
                'result.requestingDoctor:id,name',
                'result.reportSigner:id,name',
                'user:id,name',
            ])
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get();

        $rows = [];

        foreach ($orders as $index => $order) {
            $result = $order->result;
            $rowDate = $result?->result_date ? Carbon::parse($result->result_date) : Carbon::parse($order->created_at);

            $platesBalance = (int) TomographySupplyControl::query()
                ->whereDate('created_at', '<=', $rowDate->toDateString())
                ->sum('plates_in')
                - (int) TomographyResult::query()
                    ->whereDate('result_date', '<=', $rowDate->toDateString())
                    ->sum('plates_used');

            $iopamidolBalance = (float) TomographySupplyControl::query()
                ->whereDate('created_at', '<=', $rowDate->toDateString())
                ->sum('iopamidol_in')
                - (float) TomographyResult::query()
                    ->whereDate('result_date', '<=', $rowDate->toDateString())
                    ->sum('iopamidol_used');

            $paymentType = (string) $order->payment_type;
            $total = (float) ($order->total ?? 0);

            $rows[] = [
                'numero' => $index + 1,
                'fecha' => $rowDate->format('d/m/Y'),
                'paciente' => trim(($order->patient->last_name ?? '') . ' ' . ($order->patient->first_name ?? '')),
                'dni' => $order->patient->dni ?? '-',
                'orden_servicio' => $order->code,
                'tipo_tomografia' => $order->items->pluck('radiography.description')->filter()->join(', '),
                'sc_cc' => (float) ($result?->iopamidol_used ?? 0) > 0 ? 'C/C' : 'S/C',
                'uso_iopamidol' => (float) ($result?->iopamidol_used ?? 0),
                'convenio' => $order->agreement->description ?? '-',
                'servicio' => $this->mapServiceType($order->service_type),
                'efectivo' => $paymentType === 'CASH' ? $total : 0,
                'yape' => $paymentType === 'YAPE' ? $total : 0,
                'transferencia' => $paymentType === 'TRANSFER' ? $total : 0,
                'por_cobrar' => $paymentType === 'PENDING_PAYMENT' ? $total : 0,
                'placas_entregadas' => (int) ($result?->plates_used ?? 0),
                'saldo_placas' => $platesBalance,
                'saldo_iopamidol' => round($iopamidolBalance, 2),
                'medico_solicitante' => $result?->requestingDoctor?->name ?? '-',
                'doctor_informe' => $result?->reportSigner?->name ?? '-',
                'medio' => $this->mapCareMedium($order->care_medium),
                'boleta_factura' => $this->mapDocumentType($order->document_type),
                'numero_doc' => $order->document_number ?? '-',
                'recepcion' => $order->user->name ?? '-',
            ];
        }

        return $rows;
    }

    private function mapServiceType(?string $type): string
    {
        return match ($type) {
            'EMERGENCY' => 'Emergencia',
            'AGREEMENT' => 'Convenio',
            default => 'Particular',
        };
    }

    private function mapCareMedium(?string $medium): string
    {
        return match ($medium) {
            'AMBULANCE' => 'Ambulancia',
            default => 'Ambulatorio',
        };
    }

    private function mapDocumentType(?string $type): string
    {
        return match ($type) {
            'RECEIPT' => 'Boleta',
            'INVOICE' => 'Factura',
            default => '-',
        };
    }
}
