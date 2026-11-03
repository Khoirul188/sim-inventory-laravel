<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk — {{ config('app.name', 'SIM Inventory') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased bg-canvas">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8">
            <!-- Identitas Aplikasi -->
            <div class="flex flex-col items-center text-center mb-6">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-ink text-primary-400 grid place-items-center shadow-card">
                        <x-icon name="cube" class="w-6 h-6 text-primary-400" />
                    </div>
                    <span class="text-2xl font-bold text-ink tracking-tight">SIM Inventory</span>
                </a>
                <p class="text-sm text-subtle mt-1.5">Sistem Informasi Stok Barang</p>
            </div>

            <!-- Kartu Login -->
            <div class="w-full max-w-md bg-white rounded-card shadow-card border border-line/60 p-6 md:p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
