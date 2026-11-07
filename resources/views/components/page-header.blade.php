@props([
    'title',
    'breadcrumbs' => [],
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-ink">{{ $title }}</h1>
        @if (count($breadcrumbs) > 0)
            <nav aria-label="Breadcrumb" class="mt-1">
                <ol class="flex items-center flex-wrap gap-1.5 text-sm text-subtle">
                    <li>
                        <a href="{{ route('dashboard') }}" class="hover:text-ink transition-colors duration-150">Beranda</a>
                    </li>
                    @foreach ($breadcrumbs as $label => $url)
                        <li class="select-none">›</li>
                        <li>
                            @if ($url && ! $loop->last)
                                <a href="{{ $url }}" class="hover:text-ink transition-colors duration-150">{{ $label }}</a>
                            @else
                                <span class="text-ink font-medium">{{ is_int($label) ? $url : $label }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>

    @if ($slot->isNotEmpty() || isset($actions))
        <div class="w-full sm:w-auto flex flex-wrap items-center gap-3">
            {{ $slot }}
            {{ $actions ?? '' }}
        </div>
    @endif
</div>

