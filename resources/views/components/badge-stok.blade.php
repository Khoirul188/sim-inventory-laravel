@props(['product'])

@php
    $status = $product->status_stok;
    $labels = [
        'aman' => 'Aman',
        'menipis' => 'Menipis',
        'habis' => 'Habis',
    ];
@endphp

<x-badge :variant="$status" {{ $attributes }}>
    {{ $labels[$status] ?? 'Aman' }}
</x-badge>

