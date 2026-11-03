@props(['name'])

@php
    $iconName = str_starts_with($name, 'heroicon-') ? $name : 'heroicon-o-' . $name;
    $classes = 'w-5 h-5 shrink-0 ' . $attributes->get('class', '');
    $extraAttributes = $attributes->except('class')->getAttributes();
@endphp

{{ svg($iconName, trim($classes), $extraAttributes) }}
