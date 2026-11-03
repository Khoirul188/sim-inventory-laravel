@props(['title' => null, 'action' => null])

<section {{ $attributes->merge(['class' => 'bg-white rounded-card shadow-card border border-line/60 p-5 md:p-6']) }}>
    @isset($title)
        <header class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold text-ink">{{ $title }}</h2>
            @isset($action)
                <div>{{ $action }}</div>
            @endisset
        </header>
    @endisset
    {{ $slot }}
</section>

