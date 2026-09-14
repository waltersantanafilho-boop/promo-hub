@extends('layouts.admin')

@section('title', $product->name)
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Produtos', 'url' => route('admin.products.index')], ['label' => $product->name]]" />@endsection

@section('content')
    <x-admin.page-header :title="$product->name" description="Detalhes do produto canônico." />
    <div class="card admin-content-card mb-4">
        <div class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2 p-3"><x-admin.state-badge :value="$product->status->value" :label="$product->status->label()" /><div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.products.index') }}">Voltar</a><a class="btn btn-sm btn-primary" href="{{ route('admin.products.edit', $product) }}">Editar</a></div></div>
        <div class="card-body p-3 p-sm-4"><dl class="row g-4 mb-0">
            <div class="col-md-6"><dt class="admin-detail-label">Slug</dt><dd class="admin-detail-value"><code>{{ $product->slug }}</code></dd></div><div class="col-md-3"><dt class="admin-detail-label">GTIN/EAN</dt><dd class="admin-detail-value">{{ $product->gtin ?: '—' }}</dd></div><div class="col-md-3"><dt class="admin-detail-label">Grupo</dt><dd class="admin-detail-value">{{ $product->productGroup?->name ?? 'Sem grupo' }}</dd></div>
            <div class="col-md-4"><dt class="admin-detail-label">Categoria</dt><dd class="admin-detail-value">{{ $product->category->name }}</dd></div><div class="col-md-4"><dt class="admin-detail-label">Marca</dt><dd class="admin-detail-value">{{ $product->brand->name }}</dd></div><div class="col-md-4"><dt class="admin-detail-label">Mesclado em</dt><dd class="admin-detail-value">{{ $product->mergedInto?->name ?? '—' }}</dd></div>
            <div class="col-12"><dt class="admin-detail-label">Descrição</dt><dd class="admin-detail-value">{{ $product->description ?: 'Sem descrição' }}</dd></div>
            <div class="col-12"><dt class="admin-detail-label">Atributos</dt><dd class="admin-detail-value">@if ($attributesJson)<pre class="admin-config-preview bg-body-tertiary rounded p-3 mb-0"><code>{{ $attributesJson }}</code></pre>@else Sem atributos @endif</dd></div>
        </dl></div>
        <div class="card-footer bg-white p-3"><form class="row g-2 align-items-end" method="POST" action="{{ route('admin.products.status', $product) }}">@csrf @method('PATCH')<div class="col-md-4"><label class="form-label admin-form-label" for="status">Alterar status</label><select class="form-select" id="status" name="status">@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $product->status->value) === $status->value)>{{ $status->label() }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label admin-form-label" for="merged_into_id">Destino da mesclagem</label><select class="form-select" id="merged_into_id" name="merged_into_id"><option value="">Selecione quando mesclado</option>@foreach ($mergeTargets as $target)<option value="{{ $target->id }}" @selected((string) old('merged_into_id', $product->merged_into_id) === (string) $target->id)>{{ $target->name }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit">Atualizar</button></div></form></div>
    </div>

    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom p-3"><h2 class="h5 mb-0">Ofertas</h2></div>
        @if ($offers->isEmpty())<x-admin.empty-state title="Nenhuma oferta" description="Este produto ainda não possui ofertas." />@else<div class="table-responsive"><table class="table table-hover admin-table"><thead><tr><th>Loja</th><th>Preço</th><th>Disponibilidade</th><th>Status</th><th>Última verificação</th><th></th></tr></thead><tbody>@foreach ($offers as $offer)<tr><td>{{ $offer->store->name }}</td><td><x-admin.money :amount="$offer->price" :currency="$offer->currency" /></td><td>{{ $offer->availability->label() }}</td><td><x-admin.state-badge :value="$offer->status->value" :label="$offer->status->label()" /></td><td>{{ $offer->last_checked_at?->format('d/m/Y H:i') ?? 'Nunca' }}</td><td class="text-end">@can('manage offers')<a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.offers.show', $offer) }}">Visualizar</a>@endcan</td></tr>@endforeach</tbody></table></div>@if ($offers->hasPages())<div class="card-footer bg-white p-3">{{ $offers->links() }}</div>@endif @endif
    </div>
@endsection
