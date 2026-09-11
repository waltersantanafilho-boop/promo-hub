@extends('layouts.admin')
@section('title', 'Nova loja')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Lojas', 'url' => route('admin.stores.index')], ['label' => 'Nova loja']]" />@endsection
@section('content')
    <x-admin.page-header title="Nova loja" description="Cadastre uma loja para organizar suas fontes e ofertas." />
    <form class="card admin-content-card" method="POST" action="{{ route('admin.stores.store') }}">@csrf<div class="card-body p-3 p-sm-4">@include('admin.stores._form', ['cancelUrl' => route('admin.stores.index')])</div></form>
@endsection
