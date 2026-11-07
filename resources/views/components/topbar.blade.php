@props([
    'title' => 'Dashboard',
    'lowStockCount' => 0,
])

@php
    $user = auth()->user();
@endphp

<header class="h-20 px-4 md:px-6 lg:px-8 flex items-center justify-between gap-2 sm:gap-4 bg-canvas" x-data="{ mobileSearchOpen: false }">
    <!-- Kiri: Tombol Menu Mobile + Judul Halaman -->
    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1 md:flex-initial">
        <button
            type="button"
            @click="sidebarOpen = true"
            aria-label="Buka menu navigasi"
            class="lg:hidden h-10 w-10 rounded-xl bg-white border border-line grid place-items-center text-ink hover:bg-slate-50 shrink-0"
        >
            <x-icon name="bars-3" class="w-5 h-5" />
        </button>
        <h1 class="text-xl sm:text-2xl font-semibold text-ink truncate">{{ $title }}</h1>
    </div>

    <!-- Kanan: Pencarian + Lonceng + Profil -->
    <div class="flex items-center gap-1.5 sm:gap-3 md:gap-4 md:flex-1 justify-end shrink-0">
        <!-- Pencarian Global (Desktop/Tablet) -->
        <form action="{{ route('products.index') }}" method="GET" class="hidden md:block w-full max-w-md relative">
            <label for="global-search" class="sr-only">Cari kode atau nama barang</label>
            <x-icon name="magnifying-glass" class="w-5 h-5 text-subtle absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
                id="global-search"
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari kode atau nama barang…"
                class="w-full h-12 rounded-xl bg-white border border-line pl-11 pr-4 text-sm text-ink placeholder:text-subtle focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
            />
        </form>

        <!-- Tombol Pencarian Mobile (40 px di < sm) -->
        <button
            type="button"
            @click="mobileSearchOpen = !mobileSearchOpen"
            aria-label="Cari barang"
            class="md:hidden h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-white border border-line grid place-items-center text-ink hover:bg-slate-50 shrink-0"
        >
            <x-icon name="magnifying-glass" class="w-5 h-5" />
        </button>

        <!-- Lonceng Peringatan Stok Menipis (40 px di < sm, 48 px di >= sm) -->
        <a
            href="{{ route('products.index', ['status' => 'menipis']) }}"
            aria-label="Ada {{ $lowStockCount }} barang stok menipis"
            class="relative h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-white border border-line grid place-items-center text-ink hover:bg-slate-50 transition-colors duration-150 shrink-0"
        >
            <x-icon name="bell" class="w-5 h-5" />
            @if ($lowStockCount > 0)
                <span class="absolute top-2 right-2 sm:top-2.5 sm:right-2.5 w-2.5 h-2.5 rounded-full bg-danger ring-2 ring-white"></span>
            @endif
        </a>

        <!-- Dropdown Profil Pengguna -->
        <div class="relative" x-data="{ dropdownOpen: false }">
            <button
                type="button"
                @click="dropdownOpen = !dropdownOpen"
                @keydown.escape.window="dropdownOpen = false"
                aria-label="Menu profil pengguna"
                class="flex items-center gap-2.5 sm:gap-3 rounded-xl p-0 sm:p-1.5 hover:bg-white/80 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-600/40"
            >
                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-800 font-semibold text-sm grid place-items-center shrink-0">
                    {{ $user?->initials }}
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-sm font-semibold text-ink leading-tight">{{ $user?->name }}</p>
                    <p class="text-xs text-subtle leading-tight mt-0.5">{{ $user?->role_label }}</p>
                </div>
                <x-icon name="chevron-down" class="hidden sm:block w-4 h-4 text-subtle" />
            </button>

            <div
                x-show="dropdownOpen"
                @click.outside="dropdownOpen = false"
                x-transition.origin.top.right.duration.150ms
                style="display: none;"
                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-pop border border-line py-1.5 z-50"
            >
                <div class="px-4 py-2 border-b border-line sm:hidden">
                    <p class="text-sm font-semibold text-ink truncate">{{ $user?->name }}</p>
                    <p class="text-xs text-subtle">{{ $user?->role_label }}</p>
                </div>
                <a
                    href="{{ route('profile.edit') }}"
                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-ink hover:bg-slate-50 transition-colors duration-150"
                >
                    <x-icon name="cog-6-tooth" class="w-4 h-4 text-subtle" />
                    <span>Profil Saya</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-danger-ink hover:bg-danger-soft/50 transition-colors duration-150"
                    >
                        <x-icon name="arrow-right-on-rectangle" class="w-4 h-4 text-danger" />
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bar Pencarian Mobile jika dibuka -->
    <div
        x-show="mobileSearchOpen"
        x-transition.duration.150ms
        style="display: none;"
        class="fixed inset-x-0 top-20 z-30 bg-canvas px-4 pb-3 border-b border-line md:hidden"
    >
        <form action="{{ route('products.index') }}" method="GET" class="relative">
            <label for="mobile-global-search" class="sr-only">Cari kode atau nama barang</label>
            <x-icon name="magnifying-glass" class="w-5 h-5 text-subtle absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
                id="mobile-global-search"
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari kode atau nama barang…"
                class="w-full h-11 rounded-xl bg-white border border-line pl-11 pr-4 text-sm text-ink placeholder:text-subtle focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
            />
        </form>
    </div>
</header>
