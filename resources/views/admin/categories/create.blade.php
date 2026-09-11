@extends('layouts.admin')

@section('title', 'Nova categoria')

@section('breadcrumbs')
    <x-admin.breadcrumb :items="[['label' => 'Categorias', 'url' => route('admin.categories.index')], ['label' => 'Nova categoria']]" />
@endsection

@section('content')
    <x-admin.page-header title="Nova categoria" description="Cadastre uma categoria e defina sua posição na hierarquia." />
    <form class="card admin-content-card" method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        <div class="card-body p-3 p-sm-4">@include('admin.categories._form', ['cancelUrl' => route('admin.categories.index')])</div>
    </form>
@endsection
