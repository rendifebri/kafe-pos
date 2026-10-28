@props(['status'])
@php
    $map = [
        'diproses' => 'bg-amber-100 text-amber-800',
        'selesai' => 'bg-emerald-100 text-emerald-800',
        'dibatalkan' => 'bg-red-100 text-red-700',
        'aktif' => 'bg-emerald-100 text-emerald-800',
        'nonaktif' => 'bg-stone-200 text-stone-600',
    ];
@endphp
<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $map[$status] ?? 'bg-stone-100 text-stone-700' }}">{{ ucfirst($status) }}</span>
