@extends('layouts.app')
@section('title', 'Dashboard')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-xl font-bold">Halo, {{ auth()->user()->name }} 👋</h2>
        <p class="text-sm text-stone-500">{{ auth()->user()->role === 'kasir' ? 'Ringkasan transaksi Anda hari ini.' : 'Ringkasan operasional kafe hari ini.' }}</p>
    </div>
    <a href="{{ route('orders.create') }}" class="btn btn-primary">＋ Transaksi Baru</a>
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wide text-stone-500">Total Omset Hari Ini</div>
        <div class="mt-2 text-2xl font-bold text-amber-800">@rp($omset)</div>
        <div class="mt-1 text-xs text-stone-400">Pesanan berstatus selesai</div>
    </div>
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wide text-stone-500">Jumlah Pesanan</div>
        <div class="mt-2 text-2xl font-bold">{{ $jumlah }}</div>
        <div class="mt-1 text-xs text-stone-400">Transaksi selesai hari ini</div>
    </div>
    <div class="card">
        <div class="text-xs font-semibold uppercase tracking-wide text-stone-500">Menu Terlaris</div>
        <div class="mt-2 truncate text-xl font-bold">{{ $terlaris->nama ?? '-' }}</div>
        <div class="mt-1 text-xs text-stone-400">{{ $terlaris ? $terlaris->qty . ' terjual (7 hari terakhir)' : 'Belum ada penjualan' }}</div>
    </div>
    <div class="card {{ $stokRendah->count() ? 'border-red-300 bg-red-50' : '' }}">
        <div class="text-xs font-semibold uppercase tracking-wide text-stone-500">Peringatan Stok Rendah</div>
        <div class="mt-2 text-2xl font-bold {{ $stokRendah->count() ? 'text-red-700' : '' }}">{{ $stokRendah->count() }} item</div>
        <div class="mt-1 text-xs text-stone-500">
            {{ $stokRendah->take(3)->map(fn ($p) => $p->nama_produk . ' (' . $p->stok . ')')->implode(', ') ?: 'Semua stok aman' }}
        </div>
    </div>
</div>

<div class="mt-6 grid gap-4 lg:grid-cols-3">
    <div class="card lg:col-span-2">
        <h3 class="mb-3 font-semibold">Omset 7 Hari Terakhir</h3>
        <div class="h-64"><canvas id="trend"></canvas></div>
    </div>
    <div class="card">
        <h3 class="mb-3 font-semibold">Distribusi Kategori (7 hari)</h3>
        <div class="h-64"><canvas id="kategori"></canvas></div>
    </div>
</div>

<div class="card mt-6">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="font-semibold">10 Transaksi Terbaru</h3>
        <a href="{{ route('orders.index') }}" class="text-sm font-medium text-amber-700 hover:underline">Lihat semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="border-b"><tr><th class="th">Faktur</th><th class="th">Waktu</th><th class="th">Kasir</th><th class="th">Tipe</th><th class="th text-right">Total</th><th class="th">Status</th></tr></thead>
            <tbody class="divide-y">
            @forelse ($recent as $o)
                <tr class="hover:bg-stone-50">
                    <td class="td font-mono text-xs"><a class="text-amber-700 hover:underline" href="{{ route('orders.show', $o) }}">{{ $o->invoice_number }}</a></td>
                    <td class="td">{{ $o->created_at->format('d/m H:i') }}</td>
                    <td class="td">{{ $o->user->name }}</td>
                    <td class="td">{{ $o->tipe_label }}{{ $o->nomor_meja ? ' · Meja ' . $o->nomor_meja : '' }}</td>
                    <td class="td text-right font-medium">@rp($o->grand_total)</td>
                    <td class="td"><x-status-badge :status="$o->status" /></td>
                </tr>
            @empty
                <tr><td colspan="6" class="td py-8 text-center text-stone-400">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rupiah = v => 'Rp ' + Number(v).toLocaleString('id-ID');

    new Chart(document.getElementById('trend'), {
        type: 'bar',
        data: { labels: @js($labels), datasets: [{ label: 'Omset', data: @js($values), backgroundColor: '#b45309', borderRadius: 6 }] },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => rupiah(c.parsed.y) } } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => (v / 1000) + 'rb' } } }
        }
    });

    new Chart(document.getElementById('kategori'), {
        type: 'doughnut',
        data: {
            labels: @js($kategori->pluck('nama')),
            datasets: [{ data: @js($kategori->pluck('total')->map(fn ($v) => (int) $v)), backgroundColor: ['#b45309', '#0f766e', '#7c3aed', '#be123c', '#475569'] }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => c.label + ': ' + rupiah(c.parsed) } } } }
    });
</script>
@endpush
