@props(['icon', 'label'])

<li {{ $attributes->class('nav-item') }}>
    <span class="nav-link admin-nav-link admin-nav-pending" aria-disabled="true">
        <x-admin.icon :name="$icon" />
        <span class="flex-grow-1">{{ $label }}</span>
        <span class="admin-pending-label">Em breve</span>
    </span>
</li>
