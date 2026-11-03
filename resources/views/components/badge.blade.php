@props(['variant' => 'neutral'])

@php
    $variants = [
        'aman' => 'bg-success-soft text-success-ink',
        'success' => 'bg-success-soft text-success-ink',
        'menipis' => 'bg-warning-soft text-warning-ink',
        'warning' => 'bg-warning-soft text-warning-ink',
        'habis' => 'bg-danger-soft text-danger-ink',
        'danger' => 'bg-danger-soft text-danger-ink',
        'masuk' => 'bg-success-soft text-success-ink',
        'in' => 'bg-success-soft text-success-ink',
        'keluar' => 'bg-primary-100 text-primary-800',
        'out' => 'bg-primary-100 text-primary-800',
        'info' => 'bg-info-soft text-info-ink',
        'primary' => 'bg-primary-100 text-primary-800',
        'neutral' => 'bg-slate-100 text-muted',
    ];

    $classes = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>

