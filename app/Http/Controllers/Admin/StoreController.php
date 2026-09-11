<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Store\Store;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = Str::limit(trim($request->string('q')->toString()), 100, '');
        $stores = Store::query()
            ->withCount('sources')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.stores.index', compact('stores', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.stores.create', ['store' => new Store]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $store = Store::query()->create($request->validated());

        return redirect()->route('admin.stores.show', $store)
            ->with('success', 'Loja criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store): View
    {
        $store->loadCount('sources');

        return view('admin.stores.show', compact('store'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store): View
    {
        return view('admin.stores.edit', compact('store'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Store $store): RedirectResponse
    {
        $store->update($request->validated());

        return redirect()->route('admin.stores.show', $store)
            ->with('success', 'Loja atualizada com sucesso.');
    }
}
