<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $isKasir = $user->role === 'kasir';
        $start = now()->subDays(6)->startOfDay();

        // Kasir hanya melihat data miliknya sendiri
        $orders = fn () => Order::query()->when($isKasir, fn ($q) => $q->where('user_id', $user->id));

        $detail = fn () => DB::table('order_details as d')
            ->join('orders as o', 'o.id', '=', 'd.order_id')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->where('o.status', 'selesai')
            ->where('o.created_at', '>=', $start)
            ->when($isKasir, fn ($q) => $q->where('o.user_id', $user->id));

        $omset = $orders()->selesai()->whereDate('created_at', today())->sum('grand_total');
        $jumlah = $orders()->selesai()->whereDate('created_at', today())->count();

        $terlaris = $detail()
            ->selectRaw('p.nama_produk as nama, SUM(d.kuantiti) as qty')
            ->groupBy('p.id', 'p.nama_produk')
            ->orderByDesc('qty')
            ->first();

        $stokRendah = Product::where('aktif', true)
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderBy('stok')
            ->get();

        $raw = $orders()->selesai()
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'grand_total'])
            ->groupBy(fn ($o) => $o->created_at->format('Y-m-d'))
            ->map(fn ($g) => $g->sum('grand_total'));

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $labels[] = $d->format('d/m');
            $values[] = (int) ($raw[$d->format('Y-m-d')] ?? 0);
        }

        $kategori = $detail()
            ->selectRaw('c.nama_kategori as nama, SUM(d.subtotal_baris) as total')
            ->groupBy('c.nama_kategori')
            ->get();

        $recent = $orders()->with('user')->latest()->limit(10)->get();

        return view('dashboard', compact(
            'omset', 'jumlah', 'terlaris', 'stokRendah', 'labels', 'values', 'kategori', 'recent'
        ));
    }
}
