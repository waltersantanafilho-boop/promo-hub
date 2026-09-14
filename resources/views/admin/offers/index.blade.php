@extends('layouts.admin')

@section('title', 'Ofertas')
@section('breadcrumbs')<x-admin.breadcrumb :items="[['label' => 'Ofertas']]" />@endsection

@section('content')
    <x-admin.page-header title="Ofertas" description="Gerencie preços e disponibilidade por loja." :new-url="route('admin.offers.create')" new-label="Nova oferta" />
    <div class="card admin-content-card">
        <div class="card-header bg-white border-bottom p-3"><form class="row g-2" method="GET" action="{{ route('admin.offers.index') }}" role="search">
            <div class="col-12 col-xl"><input class="form-control" name="q" value="{{ $search }}" maxlength="100" aria-label="Buscar ofertas" placeholder="Produto ou ID externo"></div>
            <div class="col-sm-6 col-xl-3"><select class="form-select" name="product_id" aria-label="Filtrar por produto"><option value="">Todos os produtos</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected($productId === $product->id)>{{ $product->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-xl-2"><select class="form-select" name="store_id" aria-label="Filtrar por loja"><option value="">Todas as lojas</option>@foreach ($stores as $store)<option value="{{ $store->id }}" @selected($storeId === $store->id)>{{ $store->name }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-xl-2"><select class="form-select" name="availability" aria-label="Filtrar por disponibilidade"><option value="">Toda disponibilidade</option>@foreach ($availabilities as $availability)<option value="{{ $availability->value }}" @selected($selectedAvailability === $availability)>{{ $availability->label() }}</option>@endforeach</select></div>
            <div class="col-sm-6 col-xl-2"><select class="form-select" name="status" aria-label="Filtrar por status"><option value="">Todos os status</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selectedStatus === $status)>{{ $status->label() }}</option>@endforeach</select></div>
            <div class="col-12 d-flex justify-content-end gap-2"><button class="btn btn-outline-primary" type="submit">Filtrar</button><a class="btn btn-link" href="{{ route('admin.offers.index') }}">Limpar</a></div>
        </form></div>
        @if ($offers->isEmpty())<x-admin.empty-state title="Nenhuma oferta encontrada" description="Cadastre uma oferta ou ajuste os filtros." />@else
            <div class="table-responsive"><table class="table table-hover admin-table"><thead><tr><th>Produto</th><th>Loja/Fonte</th><th>Preço</th><th>Disponibilidade</th><th>Status</th><th>Verificada</th><th class="text-end">Ações</th></tr></thead><tbody>
                @foreach ($offers as $offer)<tr><td><a class="admin-table-link" href="{{ route('admin.offers.show', $offer) }}">{{ $offer->product->name }}</a><div class="small text-body-secondary">{{ $offer->external_id }}</div></td><td>{{ $offer->store->name }}<div class="small text-body-secondary">{{ $offer->storeSource->type->label() }}{{ $offer->storeSource->provider_key ? ' · '.$offer->storeSource->provider_key : '' }}</div></td><td><x-admin.money :amount="$offer->price" :currency="$offer->currency" /></td><td>{{ $offer->availability->label() }}</td><td><x-admin.state-badge :value="$offer->status->value" :label="$offer->status->label()" /></td><td>{{ $offer->last_checked_at?->format('d/m/Y H:i') ?? 'Nunca' }}</td><td class="admin-table-actions text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.offers.show', $offer) }}">Visualizar</a> <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.offers.edit', $offer) }}">Editar</a></td></tr>@endforeach
            </tbody></table></div>
            @if ($offers->hasPages())<div class="card-footer bg-white border-top p-3">{{ $offers->links() }}</div>@endif
        @endif
    </div>
@endsection
