<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::limit(trim($request->string('q')->toString()), 100, '');
        $categoryId = $request->integer('category_id');
        $brandId = $request->integer('brand_id');
        $productGroupId = $request->integer('product_group_id');
        $status = ProductStatus::tryFrom($request->string('status')->toString());

        $products = Product::query()
            ->with(['productGroup:id,name', 'category:id,name', 'brand:id,name'])
            ->withCount('offers')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('gtin', 'like', "%{$search}%");
            }))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when($brandId > 0, fn ($query) => $query->where('brand_id', $brandId))
            ->when($productGroupId > 0, fn ($query) => $query->where('product_group_id', $productGroupId))
            ->when($status !== null, fn ($query) => $query->where('status', $status->value))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', [
            ...$this->selectionData(includeMergeTargets: false),
            'products' => $products,
            'search' => $search,
            'categoryId' => $categoryId,
            'brandId' => $brandId,
            'productGroupId' => $productGroupId,
            'selectedStatus' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            ...$this->selectionData(),
            'product' => new Product,
            'attributesJson' => null,
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::query()->create($request->payload());

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Produto criado com sucesso.');
    }

    public function show(Product $product): View
    {
        $product->load(['productGroup:id,name', 'category:id,name', 'brand:id,name', 'mergedInto:id,name']);
        $offers = $product->offers()
            ->with(['store:id,name', 'storeSource:id,store_id,type'])
            ->orderByDesc('last_checked_at')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'offers_page')
            ->withQueryString();

        $attributesJson = $product->attributes === null
            ? null
            : json_encode($product->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return view('admin.products.show', [
            'product' => $product,
            'offers' => $offers,
            'attributesJson' => $attributesJson,
            'statuses' => ProductStatus::cases(),
            'mergeTargets' => Product::query()->whereKeyNot($product->getKey())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Product $product): View
    {
        $attributesJson = $product->attributes === null
            ? null
            : json_encode($product->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return view('admin.products.edit', [
            ...$this->selectionData($product),
            'product' => $product,
            'attributesJson' => $attributesJson,
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->payload());

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Produto atualizado com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function selectionData(?Product $product = null, bool $includeMergeTargets = true): array
    {
        $data = [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'productGroups' => ProductGroup::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => ProductStatus::cases(),
        ];

        if ($includeMergeTargets) {
            $data['mergeTargets'] = Product::query()
                ->when($product !== null, fn ($query) => $query->whereKeyNot($product->getKey()))
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return $data;
    }
}
