@extends('layouts.admin')

@section('title', 'Nova marca')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Marcas', 'url' => route('admin.brands.index')], ['label' => 'Nova marca']]" />@endsection
@section('content')
    <x-admin.page-header title="Nova marca" description="Cadastre uma marca do catálogo." />
    <form class="card admin-content-card" method="POST" action="{{ route('admin.brands.store') }}">@csrf<div class="card-body p-3 p-sm-4">@include('admin.brands._form', ['cancelUrl' => route('admin.brands.index')])</div></form>
@endsection
