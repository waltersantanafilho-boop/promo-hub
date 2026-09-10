@props(['items' => []])

<nav {{ $attributes->class('admin-breadcrumb') }} aria-label="Breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administração</a></li>
        @foreach ($items as $item)
            @if (! $loop->last && isset($item['url']))
                <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @else
                <li class="breadcrumb-item active" @if ($loop->last) aria-current="page" @endif>{{ $item['label'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
