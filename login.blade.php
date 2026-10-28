<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Titik Temu Kopi POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-stone-900 via-stone-800 to-amber-900 p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-8 shadow-2xl">
        <div class="mb-6 text-center">
            <div class="text-4xl">☕</div>
            <h1 class="mt-2 text-xl font-bold text-stone-800">Titik Temu Kopi</h1>
            <p class="text-sm text-stone-500">Masuk ke sistem kasir</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-stone-500">Username / Email</label>
                <input name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                       class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300 {{ $errors->has('login') ? 'border-red-400' : 'border-stone-300' }}">
                @error('login') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase text-stone-500">Password</label>
                <input type="password" name="password" required autocomplete="current-password"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="remember" class="rounded"> Ingat saya
            </label>
            <button class="w-full rounded-lg bg-amber-700 py-2.5 text-sm font-semibold text-white hover:bg-amber-800">Masuk</button>
        </form>

        <div class="mt-6 rounded-lg bg-stone-50 p-3 text-xs text-stone-500">
            <div class="mb-1 font-semibold text-stone-600">Akun demo</div>
            <div>Admin: <code>admin</code> / <code>password</code></div>
            <div>Kasir: <code>kasir</code> / <code>password</code></div>
        </div>
    </div>
</body>
</html>
