<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administração') · {{ config('app.name', 'PromoHub') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-shell">
    <a class="visually-hidden-focusable admin-skip-link" href="#main-content">Pular para o conteúdo</a>

    <div class="admin-container">
        @include('admin.partials.sidebar')

        <div class="admin-workspace">
            @include('admin.partials.navbar')

            <main id="main-content" class="admin-main" tabindex="-1">
                @section('breadcrumbs')
                    <x-admin.breadcrumb :items="[['label' => 'Dashboard']]" />
                @show

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">Revise os campos destacados e tente novamente.</div>
                @endif

                @yield('content')
            </main>

            <footer class="admin-footer d-flex flex-wrap justify-content-between gap-2">
                <span>{{ config('app.name', 'PromoHub') }} <span class="mx-1" aria-hidden="true">·</span> Administração</span>
                <span>Acesso restrito a usuários autorizados</span>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
