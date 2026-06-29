<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportFilterService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request, ReportService $service, ReportFilterService $filterService): View
    {
        $filters = $this->filters($request, $filterService);

        return view('admin.reports.sales', [...$service->sales($filters), ...compact('filters', 'service')]);
    }

    public function orders(Request $request, ReportService $service, ReportFilterService $filterService): View
    {
        $filters = $this->filters($request, $filterService);

        return view('admin.reports.orders', [...$service->orders($filters), ...compact('filters', 'service')]);
    }

    public function productSales(Request $request, ReportService $service, ReportFilterService $filterService): View
    {
        $filters = $this->filters($request, $filterService, true);

        return view('admin.reports.product-sales', [
            ...$service->productSales($filters),
            ...$service->filterOptions(),
            ...compact('filters', 'service'),
        ]);
    }

    public function inventory(Request $request, ReportService $service, ReportFilterService $filterService): View
    {
        $filters = $this->inventoryFilters($request, $filterService);

        return view('admin.reports.inventory', [
            ...$service->inventory($filters),
            ...$service->filterOptions(),
            ...compact('filters', 'service'),
        ]);
    }

    public function coupons(Request $request, ReportService $service, ReportFilterService $filterService): View
    {
        $filters = $this->filters($request, $filterService);

        return view('admin.reports.coupons', [...$service->coupons($filters), ...compact('filters', 'service')]);
    }

    public function taxes(Request $request, ReportService $service, ReportFilterService $filterService): View
    {
        $filters = $this->filters($request, $filterService, true);

        return view('admin.reports.taxes', [...$service->taxes($filters), ...$service->filterOptions(), ...compact('filters', 'service')]);
    }

    public function payments(Request $request, ReportService $service, ReportFilterService $filterService): View
    {
        $filters = $this->filters($request, $filterService);

        return view('admin.reports.payments', [...$service->payments($filters), ...compact('filters', 'service')]);
    }

    public function export(
        Request $request,
        string $report,
        ReportService $service,
        ReportFilterService $filterService,
    ): StreamedResponse {
        abort_unless(in_array($report, ['sales', 'orders', 'product-sales', 'inventory', 'coupons', 'taxes', 'payments'], true), Response::HTTP_NOT_FOUND);
        $filters = $report === 'inventory'
            ? $this->inventoryFilters($request, $filterService)
            : $this->filters($request, $filterService, in_array($report, ['product-sales', 'taxes'], true));
        $filename = $filterService->filename($report, $filters);

        return response()->streamDownload(function () use ($service, $report, $filters): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $service->exportHeadings($report));

            foreach ($service->exportRows($report, $filters) as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request, ReportFilterService $filterService, bool $withProduct = false): array
    {
        $rules = [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'order_status' => ['nullable', 'in:'.implode(',', ReportFilterService::ORDER_STATUSES)],
            'payment_status' => ['nullable', 'in:'.implode(',', ReportFilterService::PAYMENT_STATUSES)],
            'payment_method' => ['nullable', 'in:'.implode(',', ReportFilterService::PAYMENT_METHODS)],
        ];
        if ($withProduct) {
            $rules += [
                'product_id' => ['nullable', 'integer', 'exists:products,id'],
                'category_id' => ['nullable', 'integer', 'exists:categories,id'],
                'sku' => ['nullable', 'string', 'max:100'],
            ];
        }

        return $filterService->defaults($request->validate($rules));
    }

    private function inventoryFilters(Request $request, ReportFilterService $filterService): array
    {
        return $filterService->defaults($request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'stock_status' => ['nullable', 'in:in_stock,low_stock,out_of_stock'],
        ]), false);
    }
}
