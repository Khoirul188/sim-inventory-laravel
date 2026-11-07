@props([
    'head' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'relative overflow-x-auto']) }}>
    <table class="w-full text-left border-collapse">
        @isset($head)
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-subtle border-b border-line">
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @endisset
        <tbody class="divide-y divide-line text-sm text-muted">
            {{ $slot }}
        </tbody>
    </table>
</div>

@isset($footer)
    <div class="px-5 py-3.5 border-t border-line bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm text-muted">
        {{ $footer }}
    </div>
@endisset

