@props([
    'icon' => 'cube',
    'title' => 'Belum ada data',
    'description' => null,
    'actionText' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'py-12 px-4 text-center flex flex-col items-center justify-center']) }}>
    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-subtle grid place-items-center mb-3">
        <x-icon :name="$icon" class="w-6 h-6 text-subtle" />
    </div>
    <h3 class="text-base font-semibold text-ink">{{ $title }}</h3>
    @if ($description)
        <p class="text-sm text-subtle mt-1 max-w-sm">{{ $description }}</p>
    @endif
    @if ($actionText && $actionHref)
        <div class="mt-4">
            <x-button :href="$actionHref" variant="primary">
                {{ $actionText }}
            </x-button>
        </div>
    @endif
    {{ $slot }}
</div>

