<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Store\Enums\StoreSourceType;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSourceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Store $store): View
    {
        $search = Str::limit(trim($request->string('q')->toString()), 100, '');
        $sources = $store->sources()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('provider_key', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.store-sources.index', compact('store', 'sources', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Store $store): View
    {
        return view('admin.store-sources.create', [
            'store' => $store,
            'source' => new StoreSource,
            'types' => StoreSourceType::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSourceRequest $request, Store $store): RedirectResponse
    {
        $source = $store->sources()->create($request->payload());

        return redirect()->route('admin.stores.sources.show', [$store, $source])
            ->with('success', 'Fonte da loja criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store, StoreSource $source): View
    {
        $configJson = $source->config === null
            ? null
            : json_encode($source->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('admin.store-sources.show', compact('store', 'source', 'configJson'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store, StoreSource $source): View
    {
        $configJson = $source->config === null
            ? null
            : json_encode($source->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('admin.store-sources.edit', [
            'store' => $store,
            'source' => $source,
            'types' => StoreSourceType::cases(),
            'configJson' => $configJson,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreSourceRequest $request, Store $store, StoreSource $source): RedirectResponse
    {
        $source->update($request->payload());

        return redirect()->route('admin.stores.sources.show', [$store, $source])
            ->with('success', 'Fonte da loja atualizada com sucesso.');
    }
}
