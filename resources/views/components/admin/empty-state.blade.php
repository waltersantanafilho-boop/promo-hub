@props(['title' => 'Nenhum registro encontrado', 'description' => null])

<div {{ $attributes->class('admin-empty-state text-center') }}>
    <span class="admin-empty-icon"><x-admin.icon name="inbox" /></span>
    <h2 class="h6 mt-3 mb-1">{{ $title }}</h2>
    @if ($description)
        <p class="small text-body-secondary mb-0">{{ $description }}</p>
    @endif
</div>
