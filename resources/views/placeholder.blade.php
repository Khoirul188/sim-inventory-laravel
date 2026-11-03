<x-app-layout :title="$title ?? 'Halaman'">
    <x-page-header :title="$title ?? 'Halaman'" />

    <x-card>
        <x-empty-state
            icon="cube"
            :title="$title ?? 'Halaman'"
            description="Modul ini akan diimplementasikan pada fase berikutnya sesuai urutan pengerjaan."
        />
    </x-card>
</x-app-layout>

