<?php

namespace App\Http\Requests\Admin;

use App\Domain\Catalog\Enums\OfferAvailability;
use App\Domain\Catalog\Enums\OfferStatus;
use App\Domain\Catalog\Offer;
use App\Domain\Catalog\Product;
use App\Domain\Store\Store;
use App\Domain\Store\StoreSource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $offer = $this->route('offer');
        $storeId = $this->integer('store_id');

        return [
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'store_id' => ['required', 'integer', Rule::exists(Store::class, 'id')],
            'store_source_id' => [
                'required',
                'integer',
                Rule::exists(StoreSource::class, 'id')->where(
                    fn (Builder $query) => $query->where('store_id', $storeId),
                ),
            ],
            'external_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Offer::class, 'external_id')
                    ->where(fn (Builder $query) => $query->where('store_source_id', $this->integer('store_source_id')))
                    ->ignore($offer),
            ],
            'url' => ['required', 'url:http,https', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0.0001', 'regex:/^(?:0|[1-9]\d{0,14})(?:\.\d{1,4})?$/'],
            'original_price' => ['nullable', 'numeric', 'regex:/^(?:0|[1-9]\d{0,14})(?:\.\d{1,4})?$/', 'gte:price'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'availability' => ['required', Rule::enum(OfferAvailability::class)],
            'image_url' => ['nullable', 'url:http,https', 'max:255'],
            'last_checked_at' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(OfferStatus::class)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->validated();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Selecione o produto.',
            'store_id.required' => 'Selecione a loja.',
            'store_source_id.required' => 'Selecione a fonte da loja.',
            'store_source_id.exists' => 'A fonte selecionada não pertence à loja informada.',
            'external_id.required' => 'Informe o identificador externo.',
            'external_id.unique' => 'Este identificador externo já existe para a fonte selecionada.',
            'url.required' => 'Informe a URL da oferta.',
            'url.url' => 'Informe uma URL HTTP ou HTTPS válida para a oferta.',
            'price.required' => 'Informe o preço atual.',
            'price.min' => 'O preço deve ser maior que zero.',
            'price.regex' => 'Informe um valor monetário válido.',
            'original_price.regex' => 'Informe um preço original válido.',
            'original_price.gte' => 'O preço original deve ser maior ou igual ao preço atual.',
            'currency.regex' => 'Use o código monetário ISO com três letras maiúsculas.',
            'availability.enum' => 'Selecione uma disponibilidade válida.',
            'image_url.url' => 'Informe uma URL HTTP ou HTTPS válida para a imagem.',
            'status.enum' => 'Selecione um status válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'external_id' => trim($this->string('external_id')->toString()),
            'url' => trim($this->string('url')->toString()),
            'price' => $this->normalizeMoneyInput($this->input('price')),
            'original_price' => $this->filled('original_price') ? $this->normalizeMoneyInput($this->input('original_price')) : null,
            'currency' => strtoupper(trim($this->string('currency', 'BRL')->toString())),
            'image_url' => $this->filled('image_url') ? trim($this->string('image_url')->toString()) : null,
            'last_checked_at' => $this->filled('last_checked_at') ? $this->input('last_checked_at') : null,
        ]);
    }

    private function normalizeMoneyInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $money = trim((string) $value);

        if ($money === '' || (! str_contains($money, ',') && ! str_contains($money, 'R$'))) {
            return $money;
        }

        $digits = preg_replace('/\D/', '', $money);

        if ($digits === null || $digits === '') {
            return $money;
        }

        $digits = str_pad($digits, 3, '0', STR_PAD_LEFT);
        $integer = ltrim(substr($digits, 0, -2), '0') ?: '0';

        return $integer.'.'.substr($digits, -2);
    }
}
