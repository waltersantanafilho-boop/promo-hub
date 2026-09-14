@php
    $formatMoneyInput = static function (mixed $value): string {
        $money = trim((string) $value);

        if ($money === '' || str_contains($money, ',') || str_contains($money, 'R$')) {
            return $money;
        }

        [$integer, $fraction] = array_pad(explode('.', $money, 2), 2, '');
        $integer = ltrim($integer, '0') ?: '0';
        $integer = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $integer) ?? $integer;
        $cents = substr(str_pad($fraction, 2, '0'), 0, 2);

        return "R$ {$integer},{$cents}";
    };

    $priceInputValue = $formatMoneyInput(old('price', $offer->price));
    $originalPriceInputValue = $formatMoneyInput(old('original_price', $offer->original_price));
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label admin-form-label" for="product_id">Produto</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('product_id')]) id="product_id" name="product_id" required><option value="">Selecione</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected((string) old('product_id', $offer->product_id) === (string) $product->id)>{{ $product->name }}</option>@endforeach</select>
        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label admin-form-label" for="store_id">Loja</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('store_id')]) id="store_id" name="store_id" required><option value="">Selecione</option>@foreach ($stores as $store)<option value="{{ $store->id }}" @selected((string) old('store_id', $offer->store_id) === (string) $store->id)>{{ $store->name }}</option>@endforeach</select>
        @error('store_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label admin-form-label" for="store_source_id">Fonte da loja</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('store_source_id')]) id="store_source_id" name="store_source_id" required><option value="">Selecione a loja primeiro</option>@foreach ($stores as $store)@foreach ($store->sources as $source)<option value="{{ $source->id }}" data-store-id="{{ $store->id }}" @selected((string) old('store_source_id', $offer->store_source_id) === (string) $source->id)>{{ $source->type->label() }}{{ $source->provider_key ? ' · '.$source->provider_key : '' }}</option>@endforeach @endforeach</select>
        @error('store_source_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Somente fontes pertencentes à loja selecionada ficam disponíveis.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label admin-form-label" for="external_id">ID externo</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('external_id')]) id="external_id" name="external_id" value="{{ old('external_id', $offer->external_id) }}" maxlength="255" required>
        @error('external_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label admin-form-label" for="url">URL da oferta</label>
        <input @class(['form-control', 'is-invalid' => $errors->has('url')]) type="url" id="url" name="url" value="{{ old('url', $offer->url) }}" maxlength="2048" placeholder="https://" required>
        @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6 col-lg-3">
        <label class="form-label admin-form-label" for="price">Preço atual</label>
        <input @class(['form-control js-money-mask', 'is-invalid' => $errors->has('price')]) id="price" name="price" value="{{ $priceInputValue }}" inputmode="numeric" autocomplete="off" maxlength="25" placeholder="R$ 0,00" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="admin-form-help mt-1">Digite apenas números; os dois últimos dígitos são os centavos.</div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <label class="form-label admin-form-label" for="original_price">Preço original <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <input @class(['form-control js-money-mask', 'is-invalid' => $errors->has('original_price')]) id="original_price" name="original_price" value="{{ $originalPriceInputValue }}" inputmode="numeric" autocomplete="off" maxlength="25" placeholder="R$ 0,00">
        @error('original_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6 col-lg-2">
        <label class="form-label admin-form-label" for="currency">Moeda</label>
        <input @class(['form-control text-uppercase', 'is-invalid' => $errors->has('currency')]) id="currency" name="currency" value="{{ old('currency', $offer->currency ?? 'BRL') }}" minlength="3" maxlength="3" required>
        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6 col-lg-2">
        <label class="form-label admin-form-label" for="availability">Disponibilidade</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('availability')]) id="availability" name="availability" required>@foreach ($availabilities as $availability)<option value="{{ $availability->value }}" @selected(old('availability', $offer->availability?->value ?? 'unknown') === $availability->value)>{{ $availability->label() }}</option>@endforeach</select>
        @error('availability')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-6 col-lg-2">
        <label class="form-label admin-form-label" for="status">Status</label>
        <select @class(['form-select', 'is-invalid' => $errors->has('status')]) id="status" name="status" required>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $offer->status?->value ?? 'active') === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-7">
        <label class="form-label admin-form-label" for="image_url">Imagem da oferta <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <input @class(['form-control', 'is-invalid' => $errors->has('image_url')]) type="url" id="image_url" name="image_url" value="{{ old('image_url', $offer->image_url) }}" maxlength="255" placeholder="https://">
        @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label admin-form-label" for="last_checked_at">Última verificação <span class="fw-normal text-body-secondary">(opcional)</span></label>
        <input @class(['form-control', 'is-invalid' => $errors->has('last_checked_at')]) type="datetime-local" id="last_checked_at" name="last_checked_at" value="{{ old('last_checked_at', $offer->last_checked_at?->format('Y-m-d\TH:i')) }}">
        @error('last_checked_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="alert alert-info mt-4 mb-0" role="status">A criação registra o primeiro histórico. Na edição, um novo histórico é criado somente quando o preço realmente muda.</div>
<div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="{{ $cancelUrl }}">Cancelar</a><button class="btn btn-primary" type="submit">Salvar oferta</button></div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const store = document.getElementById('store_id');
            const source = document.getElementById('store_source_id');
            const moneyInputs = Array.from(document.querySelectorAll('.js-money-mask'));
            const formatMoney = (digits) => {
                if (digits === '') return '';

                const paddedDigits = digits.replace(/^0+(?=\d)/, '').padStart(3, '0');
                const integer = paddedDigits.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                return `R$ ${integer},${paddedDigits.slice(-2)}`;
            };
            const normalizeMoney = (value) => {
                const digits = value.replace(/\D/g, '');

                if (digits === '') return '';

                const paddedDigits = digits.replace(/^0+(?=\d)/, '').padStart(3, '0');

                return `${paddedDigits.slice(0, -2)}.${paddedDigits.slice(-2)}`;
            };
            const synchronizeSources = () => {
                const selectedStore = store.value;
                Array.from(source.options).forEach((option, index) => {
                    if (index === 0) return;
                    option.disabled = option.dataset.storeId !== selectedStore;
                    option.hidden = option.disabled;
                });
                if (source.selectedOptions[0]?.disabled) source.value = '';
            };
            store.addEventListener('change', synchronizeSources);
            synchronizeSources();

            moneyInputs.forEach((input) => {
                input.addEventListener('input', () => {
                    input.value = formatMoney(input.value.replace(/\D/g, ''));
                });
            });

            document.getElementById('price').form.addEventListener('submit', () => {
                moneyInputs.forEach((input) => {
                    input.value = normalizeMoney(input.value);
                });
            });
        });
    </script>
@endpush
