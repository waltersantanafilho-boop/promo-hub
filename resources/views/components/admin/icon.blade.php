@props(['name'])

<svg {{ $attributes->class('admin-icon') }} xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('dashboard')
            <path d="M3 10 12 3l9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z" />
            @break
        @case('layers')
            <path d="m12 3 10 5-10 5L2 8Zm-10 9 10 5 10-5M2 16l10 5 10-5" />
            @break
        @case('box')
            <path d="m12 3 9 5v9l-9 5-9-5V8Zm0 10v9M3 8l9 5 9-5M8 5l9 5" />
            @break
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="3" width="7" height="7" rx="1.5" />
            <rect x="3" y="14" width="7" height="7" rx="1.5" />
            <rect x="14" y="14" width="7" height="7" rx="1.5" />
            @break
        @case('brand')
            <circle cx="12" cy="9" r="6" />
            <path d="m8 14-1 8 5-3 5 3-1-8" />
            @break
        @case('tag')
            <path d="M3 3h8l10 10-8 8L3 11Z" />
            <circle cx="7.5" cy="7.5" r="1" />
            @break
        @case('store')
            <path d="M4 10v11h16V10M9 21v-7h6v7M4 3h16l2 5a3 3 0 0 1-5 2 3 3 0 0 1-5 0 3 3 0 0 1-5 0 3 3 0 0 1-5-2Z" />
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
            @break
        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break
        @case('logout')
            <path d="M9 3H4v18h5m5-14 5 5-5 5M8 12h13" />
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-4-4" />
            @break
        @case('inbox')
            <path d="M4 4h16v16H4Z" />
            <path d="m4 14 4-4h8l4 4M8 14h8" />
            @break
        @case('external')
            <path d="M14 3h7v7M10 14 21 3M21 14v7H3V3h7" />
            @break
    @endswitch
</svg>
