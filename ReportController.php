<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);
        $base = $this->baseQuery($request, $from, $to);

        $orders = (clone $base)->with(['user', 'details.product'])->latest()->paginate(15)->withQueryString();

        return view('reports.index', [
            'orders' => $orders,
            'summary' => $this->summary($base),
            'categories' => Category::orderBy('nama_kategori')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function csv(Request $request)
    {
        [$from, $to] = $this->range($request);
        $orders = $this->baseQuery($request, $from, $to)->with(['user', 'details.product'])->latest()->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['No. Faktur', 'Tanggal & Jam', 'Kasir', 'Tipe Pesanan', 'Item Terjual', 'Subtotal', 'Diskon', 'Pajak', 'Grand Total', 'Metode Bayar', 'Status']);

            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->invoice_number,
                    $o->created_at->format('d/m/Y H:i'),
                    $o->user->name,
                    $o->tipe_label . ($o->nomor_meja ? " (Meja {$o->nomor_meja})" : ''),
                    $this->items($o),
                    $o->total_subtotal,
                    $o->diskon_nominal,
                    $o->pajak_nominal,
                    $o->grand_total,
                    $o->metode_label,
                    ucfirst($o->status),
                ]);
            }

            fclose($out);
        }, "laporan-penjualan-{$from}_sd_{$to}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request)
    {
        [$from, $to] = $this->range($request);
        $base = $this->baseQuery($request, $from, $to);
        $orders = (clone $base)->with(['user', 'details.product'])->latest()->get();

        return Pdf::loadView('reports.pdf', [
            'orders' => $orders,
            'summary' => $this->summary($base),
            'from' => $from,
            'to' => $to,
        ])->setPaper('a4', 'landscape')->download("laporan-penjualan-{$from}_sd_{$to}.pdf");
    }

    private function range(Request $request): array
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        return [$from, $to];
    }

    private function baseQuery(Request $request, string $from, string $to)
    {
        return Order::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when($request->filled('metode_bayar'), fn ($q) => $q->where('metode_bayar', $request->metode_bayar))
            ->when($request->filled('category_id'), fn ($q) => $q->whereHas(
                'details.product',
                fn ($p) => $p->where('category_id', $request->category_id)
            ));
    }

    /** Ringkasan hanya menghitung pesanan yang TIDAK dibatalkan. */
    private function summary($base): array
    {
        $valid = (clone $base)->where('status', '!=', 'dibatalkan');

        $row = (clone $valid)->reorder()->selectRaw(
            'COALESCE(SUM(total_subtotal),0) as bruto, COALESCE(SUM(diskon_nominal),0) as diskon, '
            . 'COALESCE(SUM(pajak_nominal),0) as pajak, COALESCE(SUM(grand_total),0) as grand, COUNT(*) as jumlah'
        )->first();

        $laba = OrderDetail::whereIn('order_id', (clone $valid)->reorder()->select('orders.id'))
            ->selectRaw('COALESCE(SUM(kuantiti * (harga_satuan - harga_pokok)),0) as laba')
            ->value('laba');

        return [
            'bruto' => (int) $row->bruto,
            'diskon' => (int) $row->diskon,
            'pajak' => (int) $row->pajak,
            'bersih' => (int) $row->bruto - (int) $row->diskon, // omset bersih = bruto - diskon (tanpa pajak)
            'aov' => $row->jumlah > 0 ? (int) round($row->grand / $row->jumlah) : 0,
            'jumlah' => (int) $row->jumlah,
            'laba_kotor' => (int) $laba - (int) $row->diskon,
        ];
    }

    private function items(Order $o): string
    {
        return $o->details->map(fn ($d) => $d->product->nama_produk . ' x' . $d->kuantiti)->implode(', ');
    }
}
