@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'required' => false,
    'help' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $name && $errors->has($name);
    $inputClasses = 'h-10 w-full rounded-lg border bg-white px-3 text-sm text-ink placeholder:text-subtle focus:ring-2 transition-colors duration-150 disabled:bg-slate-50 disabled:text-subtle ' . ($hasError ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20');
@endphp

@if ($label)
    <div>
        <x-label :for="$inputId" :value="$label" :required="$required" />
        <input
            @disabled($disabled)
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @required($required)
            {{ $attributes->merge(['class' => $inputClasses]) }}
        />
        @if ($help)
            <p class="text-xs text-subtle mt-1">{{ $help }}</p>
        @endif
        @if ($name)
            <x-input-error :messages="$errors->get($name)" />
        @endif
    </div>
@else
    <input
        @disabled($disabled)
        @if($inputId) id="{{ $inputId }}" @endif
        @if($name) name="{{ $name }}" @endif
        type="{{ $type }}"
        @required($required)
        {{ $attributes->merge(['class' => $inputClasses]) }}
    />
@endif

