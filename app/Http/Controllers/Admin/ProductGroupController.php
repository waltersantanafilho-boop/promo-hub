<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\ProductGroup;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductGroupRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductGroupController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::limit(trim($request->string('q')->toString()), 100, '');
        $categoryId = $request->integer('category_id');
        $brandId = $request->integer('brand_id');
        $status = in_array($request->input('status'), ['active', 'inactive'], true)
            ? $request->string('status')->toString()
            : '';

        $productGroups = ProductGroup::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when($brandId > 0, fn ($query) => $query->where('brand_id', $brandId))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.product-groups.index', [
            'productGroups' => $productGroups,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'categoryId' => $categoryId,
            'brandId' => $brandId,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.product-groups.create', $this->formData(new ProductGroup));
    }

    public function store(ProductGroupRequest $request): RedirectResponse
    {
        $productGroup = ProductGroup::query()->create($request->validated());

        return redirect()->route('admin.product-groups.show', $productGroup)
            ->with('success', 'Grupo de produtos criado com sucesso.');
    }

    public function show(ProductGroup $productGroup): View
    {
        $productGroup->load(['category:id,name', 'brand:id,name'])->loadCount('products');

        return view('admin.product-groups.show', compact('productGroup'));
    }

    public function edit(ProductGroup $productGroup): View
    {
        return view('admin.product-groups.edit', $this->formData($productGroup));
    }

    public function update(ProductGroupRequest $request, ProductGroup $productGroup): RedirectResponse
    {
        $productGroup->update($request->validated());

        return redirect()->route('admin.product-groups.show', $productGroup)
            ->with('success', 'Grupo de produtos atualizado com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(ProductGroup $productGroup): array
    {
        return [
            'productGroup' => $productGroup,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
