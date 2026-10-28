<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
    h1 { font-size: 16px; margin: 0; } .muted { color: #777; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #78350f; color: #fff; padding: 5px; text-align: left; font-size: 8.5px; }
    td { border-bottom: 1px solid #ddd; padding: 4px 5px; vertical-align: top; }
    tr:nth-child(even) td { background: #faf7f2; }
    .r { text-align: right; } .cancel td { color: #999; text-decoration: line-through; }
    .sum { width: 100%; margin: 10px 0; } .sum td { border: 1px solid #ddd; padding: 6px; text-align: center; background: #fff; }
    .sum b { display: block; font-size: 12px; }
</style>
</head>
<body>
<h1>Laporan Penjualan · Titik Temu Kopi</h1>
<div class="muted">Periode {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }} · Dicetak {{ now()->format('d/m/Y H:i') }}</div>

<table class="sum"><tr>
    <td>Omset Bruto<b>Rp {{ number_format($summary['bruto'], 0, ',', '.') }}</b></td>
    <td>Total Diskon<b>Rp {{ number_format($summary['diskon'], 0, ',', '.') }}</b></td>
    <td>Pajak Terkumpul<b>Rp {{ number_format($summary['pajak'], 0, ',', '.') }}</b></td>
    <td>Omset Bersih<b>Rp {{ number_format($summary['bersih'], 0, ',', '.') }}</b></td>
    <td>Rata-rata Transaksi<b>Rp {{ number_format($summary['aov'], 0, ',', '.') }}</b></td>
    <td>Laba Kotor<b>Rp {{ number_format($summary['laba_kotor'], 0, ',', '.') }}</b></td>
</tr></table>

<table>
    <thead><tr><th>No. Faktur</th><th>Tanggal & Jam</th><th>Kasir</th><th>Tipe</th><th>Item Terjual</th><th class="r">Subtotal</th><th class="r">Pajak</th><th class="r">Grand Total</th><th>Status</th></tr></thead>
    <tbody>
    @foreach ($orders as $o)
        <tr class="{{ $o->status === 'dibatalkan' ? 'cancel' : '' }}">
            <td>{{ $o->invoice_number }}</td>
            <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $o->user->name }}</td>
            <td>{{ $o->tipe_label }}{{ $o->nomor_meja ? ' (M' . $o->nomor_meja . ')' : '' }}</td>
            <td>{{ $o->details->map(fn ($d) => $d->product->nama_produk . ' x' . $d->kuantiti)->implode(', ') }}</td>
            <td class="r">{{ number_format($o->total_subtotal, 0, ',', '.') }}</td>
            <td class="r">{{ number_format($o->pajak_nominal, 0, ',', '.') }}</td>
            <td class="r">{{ number_format($o->grand_total, 0, ',', '.') }}</td>
            <td>{{ ucfirst($o->status) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
