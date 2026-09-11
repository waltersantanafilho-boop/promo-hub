@props(['title', 'description' => null, 'newUrl' => null, 'newLabel' => 'Novo'])

<div {{ $attributes->class('admin-page-heading d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4') }}>
    <div>
        <h1 class="mb-1">{{ $title }}</h1>
        @if ($description)
            <p class="text-body-secondary mb-0">{{ $description }}</p>
        @endif
    </div>

    @if ($newUrl)
        <a class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 align-self-sm-start" href="{{ $newUrl }}">
            <x-admin.icon name="plus" />
            {{ $newLabel }}
        </a>
    @endif
</div>
