@props(['lowStockCount' => 0])

@php
    $user = auth()->user();
    $isAdmin = $user?->hasRole('admin');
    $isStaff = $user?->hasRole('staff');
    $isPimpinan = $user?->hasRole('pimpinan');
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 w-60 bg-ink flex flex-col px-3 py-5 transition-transform duration-200 ease-out"
    aria-label="Navigasi Utama"
>
    <!-- Logo -->
    <div class="flex items-center justify-between px-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/10 grid place-items-center text-primary-400">
                <x-icon name="cube" class="w-5 h-5 text-primary-400" />
            </div>
            <span class="text-white text-lg font-semibold tracking-tight">SIM Inventory</span>
        </a>
        <button
            type="button"
            @click="sidebarOpen = false"
            class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg"
            aria-label="Tutup menu"
        >
            <x-icon name="x-mark" class="w-5 h-5" />
        </button>
    </div>

    <!-- Daftar Menu -->
    <nav class="flex-1 overflow-y-auto space-y-0.5 mt-2">
        <!-- Grup MENU -->
        <p class="px-3 mt-4 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">MENU</p>
        <a
            href="{{ route('dashboard') }}"
            class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <x-icon name="squares-2x2" class="w-5 h-5" />
            <span>Dashboard</span>
        </a>

        <!-- Grup DATA MASTER -->
        <p class="px-3 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">DATA MASTER</p>
        <a
            href="{{ route('products.index') }}"
            class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('products.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <x-icon name="cube" class="w-5 h-5" />
            <span>Barang</span>
            @if ($lowStockCount > 0)
                <span class="ml-auto bg-warning text-white text-[11px] font-semibold rounded-full px-2 py-0.5 tabular-nums">
                    {{ $lowStockCount }}
                </span>
            @endif
        </a>

        @if ($isAdmin || $isStaff)
            <a
                href="{{ route('categories.index') }}"
                class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('categories.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <x-icon name="rectangle-group" class="w-5 h-5" />
                <span>Kategori</span>
            </a>

            <a
                href="{{ route('units.index') }}"
                class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('units.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <x-icon name="scale" class="w-5 h-5" />
                <span>Satuan</span>
            </a>

            <a
                href="{{ route('suppliers.index') }}"
                class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('suppliers.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <x-icon name="truck" class="w-5 h-5" />
                <span>Supplier</span>
            </a>
        @endif

        <!-- Grup TRANSAKSI -->
        <p class="px-3 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">TRANSAKSI</p>
        @if ($isAdmin || $isStaff)
            <a
                href="{{ route('stock-ins.index') }}"
                class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('stock-ins.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <x-icon name="arrow-down-tray" class="w-5 h-5" />
                <span>Barang Masuk</span>
            </a>

            <a
                href="{{ route('stock-outs.index') }}"
                class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('stock-outs.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <x-icon name="arrow-up-tray" class="w-5 h-5" />
                <span>Barang Keluar</span>
            </a>
        @endif

        <a
            href="{{ route('stock-movements.index') }}"
            class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('stock-movements.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <x-icon name="clipboard-document-list" class="w-5 h-5" />
            <span>Kartu Stok</span>
        </a>

        <!-- Grup LAPORAN -->
        @if ($isAdmin || $isPimpinan)
            <p class="px-3 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">LAPORAN</p>
            <a
                href="{{ route('reports.show', 'stok') }}"
                class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('reports.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <x-icon name="chart-bar-square" class="w-5 h-5" />
                <span>Laporan</span>
            </a>
        @endif

        <!-- Grup PENGATURAN -->
        <p class="px-3 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">PENGATURAN</p>
        @if ($isAdmin)
            <a
                href="{{ route('users.index') }}"
                class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('users.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
            >
                <x-icon name="users" class="w-5 h-5" />
                <span>Pengguna</span>
            </a>
        @endif

        <a
            href="{{ route('profile.edit') }}"
            class="flex items-center gap-3 rounded-xl px-3 h-11 text-sm transition-colors duration-150 {{ request()->routeIs('profile.*') ? 'bg-white/10 text-white font-medium' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
        >
            <x-icon name="cog-6-tooth" class="w-5 h-5" />
            <span>Profil Saya</span>
        </a>
    </nav>

    <!-- Tombol Keluar di dasar sidebar -->
    <div class="mt-auto pt-4 border-t border-white/10">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center gap-3 rounded-xl px-3 h-11 text-sm text-slate-300 hover:bg-white/5 hover:text-white transition-colors duration-150"
            >
                <x-icon name="arrow-right-on-rectangle" class="w-5 h-5" />
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

