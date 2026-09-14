<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Store\Store;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfferRequest;
use App\Services\Catalog\OfferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    public function __construct(private readonly OfferService $offerService) {}

    public function index(Request $request): View
    {
        $search = Str::limit(trim($request->string('q')->toString()), 100, '');
        $productId = $request->integer('product_id');
        $storeId = $request->integer('store_id');
        $status = OfferStatus::tryFrom($request->string('status')->toString());
        $availability = OfferAvailability::tryFrom($request->string('availability')->toString());

        $offers = Offer::query()
            ->with(['product:id,name', 'store:id,name', 'storeSource:id,store_id,type,provider_key'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('external_id', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            }))
            ->when($productId > 0, fn ($query) => $query->where('product_id', $productId))
            ->when($storeId > 0, fn ($query) => $query->where('store_id', $storeId))
            ->when($status !== null, fn ($query) => $query->where('status', $status->value))
            ->when($availability !== null, fn ($query) => $query->where('availability', $availability->value))
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.offers.index', [
            ...$this->selectionData(),
            'offers' => $offers,
            'search' => $search,
            'productId' => $productId,
            'storeId' => $storeId,
            'selectedStatus' => $status,
            'selectedAvailability' => $availability,
        ]);
    }

    public function create(): View
    {
        return view('admin.offers.create', [
            ...$this->selectionData(withSources: true),
            'offer' => new Offer,
        ]);
    }

    public function store(OfferRequest $request): RedirectResponse
    {
        $offer = $this->offerService->create($request->payload());

        return redirect()->route('admin.offers.show', $offer)
            ->with('success', 'Oferta criada com sucesso e primeiro preço registrado.');
    }

    public function show(Offer $offer): View
    {
        $offer->load(['product:id,name', 'store:id,name', 'storeSource:id,store_id,type,provider_key']);
        $priceHistories = $offer->priceHistories()
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('admin.offers.show', [
            'offer' => $offer,
            'priceHistories' => $priceHistories,
            'statuses' => OfferStatus::cases(),
        ]);
    }

    public function edit(Offer $offer): View
    {
        return view('admin.offers.edit', [
            ...$this->selectionData(withSources: true),
            'offer' => $offer,
        ]);
    }

    public function update(OfferRequest $request, Offer $offer): RedirectResponse
    {
        $this->offerService->update($offer, $request->payload());

        return redirect()->route('admin.offers.show', $offer)
            ->with('success', 'Oferta atualizada com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function selectionData(bool $withSources = false): array
    {
        $stores = Store::query()
            ->when($withSources, fn ($query) => $query->with([
                'sources' => fn ($query) => $query->orderBy('type')->orderBy('provider_key')->orderBy('id'),
            ]))
            ->orderBy('name')
            ->get(['id', 'name']);

        return [
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'stores' => $stores,
            'statuses' => OfferStatus::cases(),
            'availabilities' => OfferAvailability::cases(),
        ];
    }
}
