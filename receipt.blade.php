<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk {{ $order->invoice_number }}</title>
@php $w = ['80' => '80mm', '58' => '58mm', 'a4' => '190mm'][$size]; $fs = $size === '58' ? '10px' : ($size === 'a4' ? '14px' : '12px'); @endphp
<style>
    @page { size: {{ $size === 'a4' ? 'A4' : $w . ' auto' }}; margin: {{ $size === 'a4' ? '10mm' : '2mm' }}; }
    * { box-sizing: border-box; }
    body { font-family: 'Courier New', monospace; font-size: {{ $fs }}; color: #000; margin: 0; background: #eee; }
    .paper { width: {{ $w }}; margin: 10px auto; background: #fff; padding: {{ $size === 'a4' ? '12mm' : '3mm' }}; }
    .c { text-align: center; } .r { text-align: right; } .b { font-weight: bold; }
    hr { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; padding: 1px 0; }
    .note { font-size: 0.85em; padding-left: 8px; font-style: italic; }
    .bar { text-align: center; padding: 8px; background: #fff; font-family: sans-serif; }
    .bar button, .bar a { padding: 6px 12px; margin: 0 3px; cursor: pointer; font-size: 13px; }
    @media print { body { background: #fff; } .paper { margin: 0; width: 100%; padding: 0; } .bar { display: none; } }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">🖨 Cetak</button>
    <a href="?size=80">80mm</a><a href="?size=58">58mm</a><a href="?size=a4">A4</a>
</div>

<div class="paper">
    <div class="c b" style="font-size:1.3em">TITIK TEMU KOPI</div>
    <div class="c">Jl. Contoh No. 1, Padang<br>Telp. 0751-000000</div>
    <hr>
    <table>
        <tr><td>No</td><td class="r">{{ $order->invoice_number }}</td></tr>
        <tr><td>Tanggal</td><td class="r">{{ $order->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td class="r">{{ $order->user->name }}</td></tr>
        <tr><td>Tipe</td><td class="r">{{ $order->tipe_label }}{{ $order->nomor_meja ? ' / Meja ' . $order->nomor_meja : '' }}</td></tr>
        @if ($order->nama_pemesan)<tr><td>Pemesan</td><td class="r">{{ $order->nama_pemesan }}</td></tr>@endif
    </table>
    <hr>
    <table>
        @foreach ($order->details as $d)
            <tr><td colspan="2">{{ $d->product->nama_produk }}</td></tr>
            @if ($d->catatan_khusus)<tr><td colspan="2" class="note">* {{ $d->catatan_khusus }}</td></tr>@endif
            <tr><td>&nbsp;&nbsp;{{ $d->kuantiti }} x {{ number_format($d->harga_satuan, 0, ',', '.') }}</td><td class="r">{{ number_format($d->subtotal_baris, 0, ',', '.') }}</td></tr>
        @endforeach
    </table>
    <hr>
    <table>
        <tr><td>Subtotal</td><td class="r">{{ number_format($order->total_subtotal, 0, ',', '.') }}</td></tr>
        <tr><td>Diskon</td><td class="r">-{{ number_format($order->diskon_nominal, 0, ',', '.') }}</td></tr>
        <tr><td>PB1 (10%)</td><td class="r">{{ number_format($order->pajak_nominal, 0, ',', '.') }}</td></tr>
        <tr class="b" style="font-size:1.15em"><td>TOTAL</td><td class="r">{{ number_format($order->grand_total, 0, ',', '.') }}</td></tr>
        <tr><td>Bayar ({{ $order->metode_label }})</td><td class="r">{{ number_format($order->uang_dibayar, 0, ',', '.') }}</td></tr>
        <tr><td>Kembali</td><td class="r">{{ number_format($order->uang_kembali, 0, ',', '.') }}</td></tr>
    </table>
    <hr>
    <div class="c">Terima kasih sudah berkunjung!<br>Sampai jumpa di Titik Temu ☕</div>
</div>
</body>
</html>
