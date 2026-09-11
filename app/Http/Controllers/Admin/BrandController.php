<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = Str::limit(trim($request->string('q')->toString()), 100, '');
        $brands = Brand::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.brands.index', compact('brands', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.brands.create', ['brand' => new Brand]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandRequest $request): RedirectResponse
    {
        $brand = Brand::query()->create($request->validated());

        return redirect()->route('admin.brands.show', $brand)
            ->with('success', 'Marca criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand): View
    {
        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return redirect()->route('admin.brands.show', $brand)
            ->with('success', 'Marca atualizada com sucesso.');
    }
}
