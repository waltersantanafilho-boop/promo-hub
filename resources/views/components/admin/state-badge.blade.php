@props(['value', 'label'])

@php
    $tone = match ($value) {
        'active', 'in_stock' => 'success',
        'merged', 'expired' => 'warning',
        'out_of_stock', 'removed' => 'danger',
        default => 'secondary',
    };
@endphp

<span {{ $attributes->class("badge text-bg-{$tone}") }}>{{ $label }}</span>
