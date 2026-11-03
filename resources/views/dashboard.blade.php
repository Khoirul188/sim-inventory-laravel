<x-app-layout title="Dashboard">
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_320px] gap-5">
        <!-- Kolom Utama -->
        <div class="space-y-5 min-w-0">
            <!-- Baris KPI -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                <x-stat-card
                    label="Total Jenis Barang"
                    :value="format_angka($totalProducts)"
                    badge-text="—"
                    badge-variant="neutral"
                    subtext="minggu ini"
                    icon="cube"
                />
                <x-stat-card
                    label="Total Stok"
                    :value="format_angka($totalStock)"
                    badge-text="—"
                    badge-variant="neutral"
                    subtext="7 hari terakhir"
                    icon="squares-2x2"
                />
                <x-stat-card
                    label="Barang Masuk"
                    :value="format_angka($monthlyIn)"
                    badge-text="—"
                    badge-variant="neutral"
                    subtext="bulan ini"
                    icon="arrow-down-tray"
                />
                <x-stat-card
                    label="Barang Keluar"
                    :value="format_angka($monthlyOut)"
                    badge-text="—"
                    badge-variant="neutral"
                    subtext="bulan ini"
                    icon="arrow-up-tray"
                />
            </div>

            <!-- Kerangka Panel Level Stok & Ringkasan (akan dilengkapi pada Fase 4) -->
            <x-card title="Level Stok">
                <x-slot name="action">
                    @if ($lowStockCount > 0)
                        <x-badge variant="warning">Perlu Perhatian</x-badge>
                    @else
                        <x-badge variant="success">Aman</x-badge>
                    @endif
                </x-slot>

                <x-empty-state
                    icon="check-circle"
                    title="Semua stok di atas batas minimum"
                    description="Grafik, panel level stok, dan daftar barang lengkap akan diaktifkan pada Fase 4."
                />
            </x-card>
        </div>

        <!-- Kolom Kanan -->
        <div class="space-y-5">
            <x-card title="Aktivitas Terbaru">
                <x-empty-state
                    icon="clipboard-document-list"
                    title="Belum ada aktivitas"
                    description="Riwayat transaksi terbaru akan tampil di sini."
                />
            </x-card>
        </div>
    </div>
</x-app-layout>
