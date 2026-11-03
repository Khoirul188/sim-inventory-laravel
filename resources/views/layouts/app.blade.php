<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name', 'SIM Inventory') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-canvas text-ink">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col">
            <!-- Overlay Mobile Sidebar -->
            <div
                x-show="sidebarOpen"
                x-transition.opacity.duration.200ms
                @click="sidebarOpen = false"
                style="display: none;"
                class="fixed inset-0 z-30 bg-ink/50 lg:hidden"
            ></div>

            <!-- Sidebar -->
            <x-sidebar :low-stock-count="$lowStockCount ?? 0" />

            <!-- Konten Utama -->
            <div class="lg:pl-60 flex-1 flex flex-col min-w-0">
                <x-topbar :title="$title ?? 'Dashboard'" :low-stock-count="$lowStockCount ?? 0" />

                <main class="flex-1 px-4 md:px-6 lg:px-8 py-6 w-full max-w-[1600px] mx-auto">
                    {{ $slot }}
                </main>
            </div>

            <!-- Toast Notifikasi Global -->
            <x-toast />
        </div>
    </body>
</html>
