<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', ['user' => new User(['role' => 'kasir', 'status' => 'aktif'])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        User::create($data);

        return redirect()->route('users.index')->with('success', 'Akun pengguna dibuat.');
    }

    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if ($user->id === $request->user()->id && ($data['role'] !== 'admin' || $data['status'] !== 'aktif')) {
            return back()->with('error', 'Anda tidak dapat menurunkan role atau menonaktifkan akun Anda sendiri.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Akun pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->orders()->exists()) {
            $user->update(['status' => 'nonaktif']);

            return back()->with('success', 'Akun memiliki riwayat transaksi, sehingga dinonaktifkan (bukan dihapus).');
        }

        $user->delete();

        return back()->with('success', 'Akun dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => 'required|in:admin,kasir',
            'status' => 'required|in:aktif,nonaktif',
        ]);
    }
}
