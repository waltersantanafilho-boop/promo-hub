<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use App\Domain\Store\Store;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'categories' => Category::query()->count(),
                'brands' => Brand::query()->count(),
                'stores' => Store::query()->count(),
                'product_groups' => ProductGroup::query()->count(),
                'products' => Product::query()->count(),
                'offers' => Offer::query()->count(),
            ],
        ]);
    }
}
