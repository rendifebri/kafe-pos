@extends('layouts.app')
@section('title', 'Detail Transaksi')

@section('content')
<div class="mx-auto max-w-3xl space-y-4">
    <div class="card">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="font-mono text-lg font-bold">{{ $order->invoice_number }}</div>
                <div class="text-sm text-stone-500">{{ $order->created_at->format('d/m/Y H:i') }} · Kasir: {{ $order->user->name }}</div>
                <div class="mt-1 text-sm">{{ $order->tipe_label }}{{ $order->nomor_meja ? ' · Meja ' . $order->nomor_meja : '' }}{{ $order->nama_pemesan ? ' · ' . $order->nama_pemesan : '' }}</div>
            </div>
            <x-status-badge :status="$order->status" />
        </div>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full">
            <thead class="border-b"><tr><th class="th">Menu</th><th class="th text-right">Harga</th><th class="th text-center">Qty</th><th class="th text-right">Subtotal</th></tr></thead>
            <tbody class="divide-y">
            @foreach ($order->details as $d)
                <tr>
                    <td class="td">{{ $d->product->nama_produk }}@if ($d->catatan_khusus)<div class="text-xs italic text-stone-400">↳ {{ $d->catatan_khusus }}</div>@endif</td>
                    <td class="td text-right">@rp($d->harga_satuan)</td>
                    <td class="td text-center">{{ $d->kuantiti }}</td>
                    <td class="td text-right">@rp($d->subtotal_baris)</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <dl class="ml-auto mt-4 w-full max-w-xs space-y-1 text-sm">
            <div class="flex justify-between"><dt>Subtotal</dt><dd>@rp($order->total_subtotal)</dd></div>
            <div class="flex justify-between"><dt>Diskon</dt><dd>− @rp($order->diskon_nominal)</dd></div>
            <div class="flex justify-between"><dt>Pajak PB1 (10%)</dt><dd>@rp($order->pajak_nominal)</dd></div>
            <div class="flex justify-between border-t pt-2 text-base font-bold"><dt>Grand Total</dt><dd>@rp($order->grand_total)</dd></div>
            <div class="flex justify-between text-stone-500"><dt>Metode</dt><dd>{{ $order->metode_label }}</dd></div>
            <div class="flex justify-between text-stone-500"><dt>Dibayar</dt><dd>@rp($order->uang_dibayar)</dd></div>
            <div class="flex justify-between text-stone-500"><dt>Kembalian</dt><dd>@rp($order->uang_kembali)</dd></div>
        </dl>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('orders.receipt', [$order, 'size' => 80]) }}" target="_blank" class="btn btn-primary">🖨 Struk 80mm</a>
        <a href="{{ route('orders.receipt', [$order, 'size' => 58]) }}" target="_blank" class="btn btn-ghost">Struk 58mm</a>
        <a href="{{ route('orders.receipt', [$order, 'size' => 'a4']) }}" target="_blank" class="btn btn-ghost">Format A4</a>

        @if ($order->status === 'diproses')
            <form method="POST" action="{{ route('orders.status', $order) }}">@csrf @method('PATCH')
                <input type="hidden" name="status" value="selesai"><button class="btn bg-emerald-600 text-white hover:bg-emerald-700">✓ Tandai Selesai</button>
            </form>
            <form method="POST" action="{{ route('orders.status', $order) }}" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">@csrf @method('PATCH')
                <input type="hidden" name="status" value="dibatalkan"><button class="btn btn-danger">Batalkan</button>
            </form>
        @endif
        <a href="{{ route('orders.index') }}" class="btn btn-ghost ml-auto">← Kembali</a>
    </div>
</div>
@endsection
