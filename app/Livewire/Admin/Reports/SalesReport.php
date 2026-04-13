<?php

namespace App\Livewire\Admin\Reports;

use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class SalesReport extends Component
{
    public string $datePreset = 'month'; // month, quarter, year, custom
    public string $customFrom = '';
    public string $customTo = '';
    public string $search = '';

    public function setPreset(string $preset): void
    {
        $this->datePreset = $preset;
    }

    public function setCustomRange(string $from, string $to): void
    {
        $this->customFrom = $from;
        $this->customTo = $to;
        $this->datePreset = 'custom';
    }

    private function getDateRange(): array
    {
        return match ($this->datePreset) {
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $this->customFrom ? Carbon::parse($this->customFrom)->startOfDay() : now()->startOfMonth(),
                $this->customTo ? Carbon::parse($this->customTo)->endOfDay() : now()->endOfMonth(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function buildQuery(Carbon $from, Carbon $to)
    {
        return OrderItem::select(
                'order_items.commodity_id',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('MIN(order_items.price) as unit_price'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->with('commodity')
            ->groupBy('order_items.commodity_id')
            ->when($this->search, fn($q) => $q->whereHas(
                'commodity', fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
            ))
            ->orderByDesc('total_revenue');
    }

    public function exportCsv(): StreamedResponse
    {
        [$from, $to] = $this->getDateRange();
        $rows = $this->buildQuery($from, $to)->get();
        $filename = 'prodeje-' . $from->format('Y-m-d') . '_' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows, $from, $to) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Přehled prodejů: ' . $from->format('d.m.Y') . ' – ' . $to->format('d.m.Y')], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['Produkt', 'Počet prodaných kusů', 'Cena za kus (Kč)', 'Celková tržba (Kč)'], ';');

            $grandTotal = 0;
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->commodity?->name ?? 'Smazaný produkt',
                    $row->total_qty,
                    number_format($row->unit_price / 100, 2, ',', ' '),
                    number_format($row->total_revenue / 100, 2, ',', ' '),
                ], ';');
                $grandTotal += $row->total_revenue;
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['CELKEM', $rows->sum('total_qty'), '', number_format($grandTotal / 100, 2, ',', ' ')], ';');
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportExcel(): StreamedResponse
    {
        [$from, $to] = $this->getDateRange();
        $rows = $this->buildQuery($from, $to)->get();
        $filename = 'prodeje-' . $from->format('Y-m-d') . '_' . $to->format('Y-m-d') . '.xls';

        return response()->streamDownload(function () use ($rows, $from, $to) {
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
            echo '<Styles>';
            echo '<Style ss:ID="Title"><Font ss:Bold="1" ss:Size="13"/></Style>';
            echo '<Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#E5E7EB" ss:Pattern="Solid"/></Style>';
            echo '<Style ss:ID="Total"><Font ss:Bold="1"/><Interior ss:Color="#F3F4F6" ss:Pattern="Solid"/></Style>';
            echo '<Style ss:ID="Money"><NumberFormat ss:Format="#,##0.00"/></Style>';
            echo '<Style ss:ID="MoneyTotal"><Font ss:Bold="1"/><Interior ss:Color="#F3F4F6" ss:Pattern="Solid"/><NumberFormat ss:Format="#,##0.00"/></Style>';
            echo '</Styles>' . "\n";
            echo '<Worksheet ss:Name="Prodeje">' . "\n";
            echo '<Table>' . "\n";

            // Title
            echo '<Row><Cell ss:StyleID="Title" ss:MergeAcross="3"><Data ss:Type="String">';
            echo htmlspecialchars('Přehled prodejů: ' . $from->format('d. m. Y') . ' – ' . $to->format('d. m. Y'));
            echo '</Data></Cell></Row>' . "\n";
            echo '<Row/>' . "\n";

            // Header
            echo '<Row>';
            foreach (['Produkt', 'Počet prodaných kusů', 'Cena za kus (Kč)', 'Celková tržba (Kč)'] as $h) {
                echo '<Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
            }
            echo '</Row>' . "\n";

            // Data rows
            $grandTotal = 0;
            $grandQty = 0;
            foreach ($rows as $row) {
                $grandTotal += $row->total_revenue;
                $grandQty   += $row->total_qty;
                echo '<Row>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row->commodity?->name ?? 'Smazaný produkt') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="Number">' . $row->total_qty . '</Data></Cell>';
                echo '<Cell ss:StyleID="Money"><Data ss:Type="Number">' . round($row->unit_price / 100, 2) . '</Data></Cell>';
                echo '<Cell ss:StyleID="Money"><Data ss:Type="Number">' . round($row->total_revenue / 100, 2) . '</Data></Cell>';
                echo '</Row>' . "\n";
            }

            // Total row
            echo '<Row>';
            echo '<Cell ss:StyleID="Total"><Data ss:Type="String">CELKEM</Data></Cell>';
            echo '<Cell ss:StyleID="Total"><Data ss:Type="Number">' . $grandQty . '</Data></Cell>';
            echo '<Cell ss:StyleID="Total"><Data ss:Type="String"></Data></Cell>';
            echo '<Cell ss:StyleID="MoneyTotal"><Data ss:Type="Number">' . round($grandTotal / 100, 2) . '</Data></Cell>';
            echo '</Row>' . "\n";

            echo '</Table>' . "\n";
            echo '</Worksheet>' . "\n";
            echo '</Workbook>';
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function render(): View
    {
        [$from, $to] = $this->getDateRange();
        $rows = $this->buildQuery($from, $to)->get();

        return view('livewire.admin.reports.sales-report', [
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
            'grandTotal' => $rows->sum('total_revenue'),
            'grandQty' => $rows->sum('total_qty'),
        ]);
    }
}
