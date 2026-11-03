@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'help' => null,
])

@php
    $selectId = $id ?? $name;
    $hasError = $name && $errors->has($name);
    $selectClasses = 'h-10 w-full rounded-lg border bg-white px-3 text-sm text-ink focus:ring-2 transition-colors duration-150 disabled:bg-slate-50 disabled:text-subtle ' . ($hasError ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20');
@endphp

@if ($label)
    <div>
        <x-label :for="$selectId" :value="$label" :required="$required" />
        <select
            @disabled($disabled)
            id="{{ $selectId }}"
            name="{{ $name }}"
            @required($required)
            {{ $attributes->merge(['class' => $selectClasses]) }}
        >
            {{ $slot }}
        </select>
        @if ($help)
            <p class="text-xs text-subtle mt-1">{{ $help }}</p>
        @endif
        @if ($name)
            <x-input-error :messages="$errors->get($name)" />
        @endif
    </div>
@else
    <select
        @disabled($disabled)
        @if($selectId) id="{{ $selectId }}" @endif
        @if($name) name="{{ $name }}" @endif
        @required($required)
        {{ $attributes->merge(['class' => $selectClasses]) }}
    >
        {{ $slot }}
    </select>
@endif

