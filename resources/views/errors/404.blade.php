<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>404 Halaman Tidak Ditemukan — {{ config('app.name', 'SIM Inventory') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased bg-canvas">
        <main class="min-h-screen flex flex-col justify-center items-center px-4 py-8">
            <div class="w-full max-w-md bg-white rounded-card shadow-card border border-line/60 p-6 sm:p-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 grid place-items-center mx-auto mb-5">
                    <x-icon name="magnifying-glass" class="w-8 h-8 text-primary-600" />
                </div>

                <p class="text-xs font-semibold uppercase tracking-wider text-primary-600">Galat 404 — Tidak Ditemukan</p>
                <h1 class="text-2xl font-bold text-ink mt-1.5">Halaman tidak ditemukan.</h1>
                <p class="text-sm text-muted mt-2 leading-relaxed">
                    Halaman atau data yang Anda cari tidak ditemukan atau mungkin telah dipindahkan. Periksa kembali alamat URL yang Anda tuju.
                </p>

                <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    @auth
                        <x-button :href="route('dashboard')" variant="primary" class="w-full sm:w-auto">
                            <x-icon name="arrow-left" class="w-4 h-4" />
                            <span>Kembali ke Dashboard</span>
                        </x-button>
                    @else
                        <x-button :href="route('login')" variant="primary" class="w-full sm:w-auto">
                            <x-icon name="arrow-right-on-rectangle" class="w-4 h-4" />
                            <span>Ke Halaman Login</span>
                        </x-button>
                    @endauth
                </div>
            </div>
        </main>
    </body>
</html>

