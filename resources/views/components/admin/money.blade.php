@props(['amount', 'currency' => 'BRL'])

@php
    [$integer, $fraction] = array_pad(explode('.', (string) $amount, 2), 2, '');
    $integer = ltrim($integer, '0') ?: '0';
    $integer = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $integer);
    $fraction = substr(str_pad($fraction, 2, '0'), 0, max(2, strlen(rtrim($fraction, '0'))));
@endphp

<span {{ $attributes }}>{{ $currency === 'BRL' ? 'R$' : $currency }} {{ $integer }},{{ $fraction }}</span>
