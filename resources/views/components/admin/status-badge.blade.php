@props(['active'])

<span {{ $attributes->class(['badge rounded-pill', 'text-bg-success' => $active, 'text-bg-secondary' => ! $active]) }}>
    {{ $active ? 'Ativo' : 'Inativo' }}
</span>
