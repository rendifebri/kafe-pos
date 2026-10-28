<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Titik Temu Kopi POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <style type="text/tailwindcss">
        @layer components {
            .input { @apply w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-200; }
            .label { @apply mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-500; }
            .btn { @apply inline-flex items-center justify-center gap-1 rounded-lg px-4 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-50; }
            .btn-primary { @apply bg-amber-700 text-white hover:bg-amber-800; }
            .btn-ghost { @apply border border-stone-300 bg-white text-stone-700 hover:bg-stone-50; }
            .btn-danger { @apply bg-red-600 text-white hover:bg-red-700; }
            .card { @apply rounded-2xl border border-stone-200 bg-white p-5 shadow-sm; }
            .th { @apply px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-stone-500; }
            .td { @apply px-3 py-2.5 text-sm; }
        }
    </style>
    <style>[x-cloak]{display:none!important}</style>
    @stack('head')
</head>
<body class="min-h-screen bg-stone-100 text-stone-800" x-data="{ nav: false }">

@php
    $u = auth()->user();
    // [route, label, icon path, role, pola route aktif]
    $links = [
        ['dashboard', 'Dashboard', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10', null, 'dashboard'],
        ['orders.create', 'Transaksi Baru', 'M12 4v16m8-8H4', null, 'orders.create'],
        ['orders.index', $u->role === 'admin' ? 'Semua Transaksi' : 'Riwayat Saya', 'M9 5h10M9 12h10M9 19h10M5 5h.01M5 12h.01M5 19h.01', null, 'orders.index,orders.show'],
        ['products.index', 'Menu & Stok', 'M4 7h16M4 12h16M4 17h10', 'admin', 'products.*'],
        ['categories.index', 'Kategori', 'M7 7h.01M3 11l8 8 10-10V3H13L3 11z', 'admin', 'categories.*'],
        ['reports.index', 'Laporan', 'M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z', 'admin', 'reports.*'],
        ['users.index', 'Pengguna', 'M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21a8 8 0 0116 0', 'admin', 'users.*'],
    ];
@endphp

<div class="lg:flex">
    <div x-show="nav" x-cloak @click="nav=false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    <aside :class="nav ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 flex w-64 transform flex-col bg-stone-900 text-stone-100 transition lg:static lg:min-h-screen lg:translate-x-0">
        <div class="px-5 py-5">
            <div class="text-lg font-bold tracking-tight">☕ Titik Temu Kopi</div>
            <div class="text-xs text-stone-400">Point of Sales</div>
        </div>
        <nav class="flex-1 space-y-1 px-3">
            @foreach ($links as [$route, $label, $icon, $only, $active])
                @if (! $only || $only === $u->role)
                    <a href="{{ route($route) }}" @click="nav=false"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs(...explode(',', $active)) ? 'bg-amber-700 text-white' : 'text-stone-300 hover:bg-stone-800' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="border-t border-stone-800 p-4">
            <div class="text-sm font-semibold">{{ $u->name }}</div>
            <div class="mb-3 text-xs uppercase text-stone-400">{{ $u->role === 'admin' ? 'Administrator' : 'Kasir / Waiter' }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn w-full bg-stone-800 text-stone-100 hover:bg-stone-700">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-stone-200 bg-white/90 px-4 py-3 backdrop-blur lg:px-8">
            <button @click="nav=true" class="rounded-lg border border-stone-300 p-2 lg:hidden" aria-label="Menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-bold">@yield('title', 'Dashboard')</h1>
            <div class="ml-auto hidden text-sm text-stone-500 sm:block">{{ now()->translatedFormat('l, d F Y') }}</div>
        </header>

        <main class="p-4 lg:p-8">@yield('content')</main>
    </div>
</div>

{{-- Toast notifikasi --}}
<div class="fixed right-4 top-4 z-50 w-80 max-w-[90vw] space-y-2">
    @foreach (['success' => 'bg-emerald-600', 'error' => 'bg-red-600'] as $key => $color)
        @if (session($key))
            <div x-data="{ s: true }" x-init="setTimeout(() => s = false, 5000)" x-show="s" x-transition
                 class="{{ $color }} flex items-start gap-2 rounded-lg px-4 py-3 text-sm text-white shadow-lg">
                <span class="flex-1">{{ session($key) }}</span>
                <button @click="s=false" class="opacity-80 hover:opacity-100">✕</button>
            </div>
        @endif
    @endforeach
    @if ($errors->any())
        <div x-data="{ s: true }" x-init="setTimeout(() => s = false, 9000)" x-show="s" x-transition
             class="rounded-lg bg-red-600 px-4 py-3 text-sm text-white shadow-lg">
            <div class="mb-1 flex justify-between font-semibold"><span>Periksa kembali isian Anda:</span><button @click="s=false">✕</button></div>
            <ul class="list-disc space-y-0.5 pl-4">
                @foreach (collect($errors->all())->take(5) as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif
</div>

@stack('scripts')
</body>
</html>
