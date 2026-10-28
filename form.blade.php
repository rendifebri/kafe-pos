@extends('layouts.app')
@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="card mx-auto max-w-xl space-y-4">
    @csrf
    @if ($user->exists) @method('PUT') @endif

    <div><label class="label">Nama Lengkap</label><input name="name" class="input" value="{{ old('name', $user->name) }}" required></div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="label">Username</label><input name="username" class="input" value="{{ old('username', $user->username) }}" required></div>
        <div><label class="label">Email</label><input type="email" name="email" class="input" value="{{ old('email', $user->email) }}" required></div>
    </div>
    <div><label class="label">Password {{ $user->exists ? '(kosongkan jika tidak diganti)' : '' }}</label><input type="password" name="password" class="input" minlength="6" {{ $user->exists ? '' : 'required' }} autocomplete="new-password"></div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="label">Role</label>
            <select name="role" class="input"><option value="kasir" @selected(old('role', $user->role) === 'kasir')>Kasir / Waiter</option><option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator / Manager</option></select>
        </div>
        <div><label class="label">Status</label>
            <select name="status" class="input"><option value="aktif" @selected(old('status', $user->status) === 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status', $user->status) === 'nonaktif')>Nonaktif</option></select>
        </div>
    </div>
    <div class="flex gap-2 border-t pt-4"><button class="btn btn-primary">Simpan</button><a href="{{ route('users.index') }}" class="btn btn-ghost">Batal</a></div>
</form>
@endsection
