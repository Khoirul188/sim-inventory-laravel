@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-600/40 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'h-10 px-4 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-800',
        'secondary' => 'h-10 px-4 rounded-lg bg-white text-ink text-sm font-medium border border-line hover:bg-slate-50',
        'danger' => 'h-10 px-4 rounded-lg bg-danger text-white text-sm font-medium hover:bg-danger-ink',
        'icon' => 'h-9 w-9 grid place-items-center rounded-lg text-subtle hover:bg-slate-100 hover:text-ink',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

