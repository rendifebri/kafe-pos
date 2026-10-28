<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $service)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::with('user')
            ->when($user->role === 'kasir', fn ($q) => $q->where('user_id', $user->id))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('invoice_number', 'like', '%' . $request->q . '%')
                  ->orWhere('nama_pemesan', 'like', '%' . $request->q . '%');
            }))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('created_at', $request->tanggal))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::with('category')->where('aktif', true)->orderBy('nama_produk')->get();

        return view('orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipe_pesanan' => 'required|in:dine_in,takeaway',
            'nomor_meja' => 'required_if:tipe_pesanan,dine_in|nullable|string|max:10',
            'nama_pemesan' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1|max:99',
            'items.*.note' => 'nullable|string|max:150',
            'diskon_tipe' => 'nullable|in:nominal,persen',
            'diskon_nilai' => 'nullable|numeric|min:0',
            'metode_bayar' => 'required|in:tunai,qris,transfer,debit',
            'uang_dibayar' => 'required_if:metode_bayar,tunai|nullable|integer|min:0',
        ], [
            'nomor_meja.required_if' => 'Nomor meja wajib diisi untuk Dine-In.',
            'items.required' => 'Tambahkan minimal satu item pesanan.',
            'items.*.product_id.required' => 'Pilih menu pada setiap baris.',
            'uang_dibayar.required_if' => 'Nominal pembayaran tunai wajib diisi.',
        ]);

        $order = $this->service->create($data, $request->user()->id);

        return redirect()->route('orders.show', $order)
            ->with('success', "Pesanan {$order->invoice_number} berhasil disimpan.");
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        $order->load(['user', 'details.product']);

        return view('orders.show', compact('order'));
    }

    public function receipt(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        $order->load(['user', 'details.product']);
        $size = in_array($request->query('size'), ['80', '58', 'a4'], true) ? $request->query('size') : '80';

        return view('orders.receipt', compact('order', 'size'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeOrder($request, $order);
        $data = $request->validate(['status' => 'required|in:selesai,dibatalkan']);

        $this->service->updateStatus($order, $data['status']);

        return back()->with('success', 'Status pesanan diperbarui menjadi ' . ucfirst($data['status']) . '.');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        $user = $request->user();
        abort_if($user->role === 'kasir' && $order->user_id !== $user->id, 403, 'Anda hanya dapat melihat transaksi Anda sendiri.');
    }
}
