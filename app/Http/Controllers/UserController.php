<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna dengan pencarian, filter peran/status, dan paginasi.
     */
    public function index(Request $request): View
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && in_array($request->role, ['admin', 'staff', 'pimpinan'], true)) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $users = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Tampilkan form tambah pengguna baru.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Simpan pengguna baru beserta perannya.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'],
            'email_verified_at' => now(),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$user->name} berhasil disimpan.");
    }

    /**
     * Tampilkan form ubah pengguna.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Perbarui data pengguna, status aktif, peran, dan kata sandi (bila diisi).
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $currentUser = $request->user();

        // BR-12: Admin tidak dapat menonaktifkan atau menurunkan peran dirinya sendiri
        if ($currentUser->id === $user->id) {
            if (! $validated['is_active'] || $validated['role'] !== 'admin') {
                return back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan atau menghapus akun Anda sendiri.');
            }
        }

        // BR-12: Pastikan minimal tersisa satu admin aktif
        if ($user->hasRole('admin') && (! $validated['is_active'] || $validated['role'] !== 'admin')) {
            $activeAdminCount = User::role('admin')->where('is_active', true)->where('id', '!=', $user->id)->count();
            if ($activeAdminCount < 1) {
                return back()->withInput()->with('error', 'Sistem harus memiliki minimal satu admin aktif.');
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        // Akhiri sesi pengguna jika akunnya dinonaktifkan
        if (! $user->is_active && Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus atau nonaktifkan pengguna.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan atau menghapus akun Anda sendiri.');
        }

        if ($user->hasRole('admin')) {
            $activeAdminCount = User::role('admin')->where('is_active', true)->where('id', '!=', $user->id)->count();
            if ($activeAdminCount < 1) {
                return back()->with('error', 'Sistem harus memiliki minimal satu admin aktif.');
            }
        }

        // PRD §5.3: Pengguna yang sudah memiliki transaksi tidak dihapus permanen, hanya dinonaktifkan
        $hasTransactions = $user->stockIns()->exists()
            || $user->stockOuts()->exists()
            || $user->stockMovements()->exists();

        if ($hasTransactions) {
            $user->update(['is_active' => false]);
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            return redirect()->route('users.index')
                ->with('warning', "Pengguna {$user->name} memiliki riwayat transaksi sehingga hanya dinonaktifkan.");
        }

        $nama = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$nama} berhasil dihapus.");
    }
}
