@extends('layouts.app')
@section('title', 'Menu & Stok')

@section('content')
<div class="mb-4 flex flex-wrap items-end justify-between gap-3">
    <form method="GET" class="flex flex-wrap items-end gap-2">
        <div><label class="label">Cari</label><input class="input" name="q" value="{{ request('q') }}" placeholder="Nama / kode"></div>
        <div><label class="label">Kategori</label>
            <select class="input" name="category_id"><option value="">Semua</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->nama_kategori }}</option>@endforeach
            </select>
        </div>
        <button class="btn btn-ghost">Filter</button>
    </form>
    <a href="{{ route('products.create') }}" class="btn btn-primary">＋ Tambah Menu</a>
</div>

<div class="card overflow-x-auto p-0">
    <table class="w-full">
        <thead class="border-b bg-stone-50"><tr><th class="th">Kode</th><th class="th">Nama</th><th class="th">Kategori</th><th class="th text-right">Harga Jual</th><th class="th text-right">HPP</th><th class="th text-right">Stok</th><th class="th">Status</th><th class="th"></th></tr></thead>
        <tbody class="divide-y">
        @forelse ($products as $p)
            <tr class="hover:bg-stone-50">
                <td class="td font-mono text-xs">{{ $p->kode_produk }}</td>
                <td class="td font-medium">{{ $p->nama_produk }}</td>
                <td class="td">{{ $p->category->nama_kategori }}</td>
                <td class="td text-right">@rp($p->harga_jual)</td>
                <td class="td text-right text-stone-500">@rp($p->harga_pokok)</td>
                <td class="td text-right">
                    <span class="{{ $p->is_low_stock ? 'rounded bg-red-100 px-2 py-0.5 font-semibold text-red-700' : '' }}">{{ $p->stok }} {{ $p->unit }}</span>
                </td>
                <td class="td"><x-status-badge :status="$p->aktif ? 'aktif' : 'nonaktif'" /></td>
                <td class="td whitespace-nowrap text-right">
                    <a href="{{ route('products.edit', $p) }}" class="btn btn-ghost px-3 py-1 text-xs">Edit</a>
                    <form method="POST" action="{{ route('products.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus menu ini?')">@csrf @method('DELETE')
                        <button class="btn btn-danger px-3 py-1 text-xs">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="td py-10 text-center text-stone-400">Belum ada menu.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
