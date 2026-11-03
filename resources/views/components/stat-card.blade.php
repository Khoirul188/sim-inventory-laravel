@props([
    'label',
    'value',
    'badgeText' => null,
    'badgeVariant' => 'neutral',
    'subtext' => null,
    'icon' => null,
])

<x-card {{ $attributes->merge(['class' => '!p-5']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-medium text-subtle">{{ $label }}</p>
        @if ($icon)
            <div class="w-9 h-9 rounded-full bg-primary-50 text-primary-600 grid place-items-center shrink-0">
                <x-icon :name="$icon" class="w-5 h-5" />
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-end justify-between gap-2">
        <p class="text-3xl font-bold text-ink tabular-nums">{{ $value }}</p>

        @if ($badgeText || $subtext)
            <div class="text-right">
                @if ($badgeText)
                    <x-badge :variant="$badgeVariant">{{ $badgeText }}</x-badge>
                @endif
                @if ($subtext)
                    <p class="text-xs text-subtle mt-1">{{ $subtext }}</p>
                @endif
            </div>
        @endif
    </div>
</x-card>

