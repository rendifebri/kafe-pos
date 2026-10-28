@extends('layouts.app')
@section('title', 'Transaksi Baru')

@section('content')
<form method="POST" action="{{ route('orders.store') }}" x-data="posForm(@js($products->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama_produk, 'harga' => (int) $p->harga_jual, 'stok' => (int) $p->stok, 'kategori' => $p->category->nama_kategori])->values()), @js(old()))"
      @submit="submitting = true" class="grid gap-5 xl:grid-cols-3">
    @csrf

    <div class="space-y-5 xl:col-span-2">
        {{-- Info pesanan --}}
        <div class="card">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <h2 class="font-semibold">Info Pesanan</h2>
                <span class="rounded bg-stone-100 px-2 py-1 font-mono text-xs text-stone-500">No. Faktur: otomatis (INV-POS-{{ now()->format('Ymd') }}-XXXX)</span>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label">Tipe Pesanan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer rounded-lg border px-3 py-2 text-center text-sm font-medium" :class="tipe==='dine_in' ? 'border-amber-700 bg-amber-50 text-amber-800' : 'border-stone-300'">
                            <input type="radio" name="tipe_pesanan" value="dine_in" x-model="tipe" class="sr-only"> Dine-In
                        </label>
                        <label class="cursor-pointer rounded-lg border px-3 py-2 text-center text-sm font-medium" :class="tipe==='takeaway' ? 'border-amber-700 bg-amber-50 text-amber-800' : 'border-stone-300'">
                            <input type="radio" name="tipe_pesanan" value="takeaway" x-model="tipe" class="sr-only"> Takeaway
                        </label>
                    </div>
                </div>
                <div x-show="tipe==='dine_in'">
                    <label class="label">No. Meja *</label>
                    <input name="nomor_meja" value="{{ old('nomor_meja') }}" :required="tipe==='dine_in'" class="input" placeholder="mis. 5">
                </div>
                <div>
                    <label class="label">Nama Pemesan / Catatan Meja</label>
                    <input name="nama_pemesan" value="{{ old('nama_pemesan') }}" class="input" placeholder="opsional">
                </div>
            </div>
        </div>

        {{-- Menu cepat --}}
        <div class="card">
            <h2 class="mb-3 font-semibold">Menu Cepat <span class="text-xs font-normal text-stone-400">(klik untuk menambah)</span></h2>
            <div class="mb-3 flex flex-wrap gap-2">
                <button type="button" class="rounded-full border px-3 py-1 text-xs font-medium" :class="cat==='' ? 'border-amber-700 bg-amber-700 text-white' : 'border-stone-300'" @click="cat=''">Semua</button>
                @foreach ($products->pluck('category.nama_kategori')->unique() as $c)
                    <button type="button" class="rounded-full border px-3 py-1 text-xs font-medium" :class="cat==='{{ $c }}' ? 'border-amber-700 bg-amber-700 text-white' : 'border-stone-300'" @click="cat='{{ $c }}'">{{ $c }}</button>
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                <template x-for="p in products.filter(p => !cat || p.kategori === cat)" :key="p.id">
                    <button type="button" @click="addProduct(p.id)" :disabled="p.stok <= 0"
                            class="rounded-lg border border-stone-200 p-2 text-left text-sm transition hover:border-amber-600 hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-40">
                        <div class="truncate font-medium" x-text="p.nama"></div>
                        <div class="text-xs text-amber-800" x-text="rp(p.harga)"></div>
                        <div class="text-xs" :class="p.stok <= 5 ? 'text-red-600' : 'text-stone-400'" x-text="p.stok <= 0 ? 'Habis' : 'Stok ' + p.stok"></div>
                    </button>
                </template>
            </div>
        </div>

        {{-- Item --}}
        <div class="card">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Item Pesanan</h2>
                <button type="button" class="btn btn-ghost px-3 py-1 text-xs" @click="add()">＋ Tambah Baris</button>
            </div>
            <div class="space-y-3">
                <template x-for="(r, i) in rows" :key="i">
                    <div class="grid gap-2 rounded-lg border border-stone-200 p-3 sm:grid-cols-12">
                        <div class="sm:col-span-5">
                            <select :name="`items[${i}][product_id]`" x-model="r.product_id" required class="input">
                                <option value="">— Pilih menu —</option>
                                <template x-for="p in products" :key="p.id">
                                    <option :value="p.id" :disabled="p.stok <= 0" x-text="`${p.nama} · ${rp(p.harga)} (stok ${p.stok})`"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <input type="number" min="1" max="99" :name="`items[${i}][qty]`" x-model.number="r.qty" required class="input" placeholder="Qty">
                        </div>
                        <div class="sm:col-span-4">
                            <input type="text" maxlength="150" :name="`items[${i}][note]`" x-model="r.note" class="input" placeholder="Catatan (Less Sugar, Extra Ice…)">
                        </div>
                        <div class="flex items-center justify-between sm:col-span-1 sm:justify-end">
                            <span class="text-sm font-semibold sm:hidden" x-text="rp(lineTotal(r))"></span>
                            <button type="button" @click="remove(i)" class="rounded p-2 text-red-600 hover:bg-red-50" title="Hapus baris">✕</button>
                        </div>
                        <div class="hidden text-right text-xs text-stone-500 sm:col-span-12 sm:block" x-show="r.product_id">Subtotal baris: <b x-text="rp(lineTotal(r))"></b></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Ringkasan & pembayaran --}}
    <div class="space-y-5">
        <div class="card xl:sticky xl:top-20">
            <h2 class="mb-3 font-semibold">Ringkasan & Pembayaran</h2>

            <div class="mb-4 grid grid-cols-3 gap-2">
                <div class="col-span-1">
                    <label class="label">Diskon</label>
                    <select name="diskon_tipe" x-model="diskonTipe" class="input"><option value="nominal">Rp</option><option value="persen">%</option></select>
                </div>
                <div class="col-span-2">
                    <label class="label">&nbsp;</label>
                    <input type="number" min="0" name="diskon_nilai" x-model.number="diskonNilai" class="input" placeholder="0">
                </div>
            </div>

            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt>Subtotal</dt><dd x-text="rp(subtotal)"></dd></div>
                <div class="flex justify-between"><dt>Diskon</dt><dd x-text="'− ' + rp(diskon)"></dd></div>
                <div class="flex justify-between"><dt>Pajak PB1 (10%)</dt><dd x-text="rp(pajak)"></dd></div>
                <div class="flex justify-between border-t pt-2 text-lg font-bold text-amber-800"><dt>Grand Total</dt><dd x-text="rp(grand)"></dd></div>
            </dl>

            <div class="mt-4">
                <label class="label">Metode Pembayaran</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach (\App\Models\Order::METODE as $val => $lbl)
                        <label class="cursor-pointer rounded-lg border px-2 py-2 text-center text-sm font-medium" :class="metode==='{{ $val }}' ? 'border-amber-700 bg-amber-50 text-amber-800' : 'border-stone-300'">
                            <input type="radio" name="metode_bayar" value="{{ $val }}" x-model="metode" class="sr-only"> {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mt-4" x-show="metode==='tunai'">
                <label class="label">Uang Diterima</label>
                <input type="number" min="0" name="uang_dibayar" x-model.number="bayar" :required="metode==='tunai'" class="input text-right text-lg font-semibold">
                <div class="mt-2 flex flex-wrap gap-1">
                    <button type="button" class="btn btn-ghost px-2 py-1 text-xs" @click="bayar = grand">Uang Pas</button>
                    <template x-for="n in [20000, 50000, 100000, 200000]"><button type="button" class="btn btn-ghost px-2 py-1 text-xs" @click="bayar = n" x-text="n/1000 + 'rb'"></button></template>
                </div>
                <div class="mt-3 rounded-lg px-3 py-2 text-sm font-semibold" :class="bayar >= grand && grand > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                    <span x-show="bayar >= grand && grand > 0">Kembalian: <span x-text="rp(bayar - grand)"></span></span>
                    <span x-show="!(bayar >= grand && grand > 0)">Kurang: <span x-text="rp(Math.max(grand - bayar, 0))"></span></span>
                </div>
            </div>

            <button class="btn btn-primary mt-5 w-full py-3 text-base" :disabled="!canSubmit || submitting">
                <span x-show="!submitting">Simpan Pesanan</span><span x-show="submitting" x-cloak>Menyimpan…</span>
            </button>
            <p class="mt-2 text-center text-xs text-stone-400">Total final dihitung ulang oleh server.</p>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function posForm(products, old) {
    const oldRows = Array.isArray(old.items) ? old.items.map(i => ({ product_id: i.product_id || '', qty: Number(i.qty) || 1, note: i.note || '' })) : [];
    return {
        products,
        cat: '',
        submitting: false,
        tipe: old.tipe_pesanan || 'dine_in',
        metode: old.metode_bayar || 'tunai',
        diskonTipe: old.diskon_tipe || 'nominal',
        diskonNilai: Number(old.diskon_nilai || 0),
        bayar: Number(old.uang_dibayar || 0),
        rows: oldRows.length ? oldRows : [{ product_id: '', qty: 1, note: '' }],

        rp: n => 'Rp ' + Number(n || 0).toLocaleString('id-ID'),
        find(id) { return this.products.find(p => p.id == id); },
        lineTotal(r) { const p = this.find(r.product_id); return p ? p.harga * (Number(r.qty) || 0) : 0; },
        add() { this.rows.push({ product_id: '', qty: 1, note: '' }); },
        remove(i) { this.rows.splice(i, 1); if (!this.rows.length) this.add(); },
        addProduct(id) {
            const existing = this.rows.find(r => r.product_id == id);
            if (existing) { existing.qty++; return; }
            const empty = this.rows.find(r => !r.product_id);
            if (empty) { empty.product_id = id; empty.qty = 1; return; }
            this.rows.push({ product_id: id, qty: 1, note: '' });
        },
        get subtotal() { return this.rows.reduce((s, r) => s + this.lineTotal(r), 0); },
        get diskon() {
            const v = Number(this.diskonNilai) || 0;
            return this.diskonTipe === 'persen' ? Math.round(this.subtotal * Math.min(v, 100) / 100) : Math.min(v, this.subtotal);
        },
        get pajak() { return Math.round((this.subtotal - this.diskon) * 0.10); },
        get grand() { return this.subtotal - this.diskon + this.pajak; },
        get canSubmit() {
            const filled = this.rows.some(r => r.product_id && r.qty > 0);
            return filled && this.grand > 0 && (this.metode !== 'tunai' || this.bayar >= this.grand);
        },
    };
}
</script>
@endpush
