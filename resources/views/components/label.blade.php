@props([
    'value' => null,
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-ink mb-1.5']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-danger">*</span>
    @endif
</label>

