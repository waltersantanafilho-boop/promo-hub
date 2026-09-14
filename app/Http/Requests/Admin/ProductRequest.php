<?php

namespace App\Http\Requests\Admin;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Product;
use App\Domain\Catalog\ProductGroup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use JsonException;

class ProductRequest extends FormRequest
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
        $product = $this->route('product');

        return [
            'product_group_id' => ['nullable', 'integer', Rule::exists(ProductGroup::class, 'id')],
            'category_id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
            'brand_id' => ['required', 'integer', Rule::exists(Brand::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(Product::class, 'slug')->ignore($product),
            ],
            'description' => ['nullable', 'string', 'max:20000'],
            'gtin' => ['nullable', 'string', 'regex:/^(?:\d{8}|\d{12,14})$/'],
            'attributes' => ['nullable', 'string', 'json', 'max:50000'],
            'canonical_image_url' => ['nullable', 'url:http,https', 'max:255'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'merged_into_id' => [
                'nullable',
                'required_if:status,'.ProductStatus::Merged->value,
                'prohibited_unless:status,'.ProductStatus::Merged->value,
                'integer',
                Rule::exists(Product::class, 'id'),
            ],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $product = $this->route('product');

            if ($product instanceof Product && $this->integer('merged_into_id') === $product->getKey()) {
                $validator->errors()->add('merged_into_id', 'Um produto não pode ser mesclado nele mesmo.');
            }

            if ($validator->errors()->has('attributes') || ! $this->filled('attributes')) {
                return;
            }

            try {
                $attributes = json_decode($this->string('attributes')->toString(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return;
            }

            if (! is_array($attributes)) {
                $validator->errors()->add('attributes', 'Os atributos devem ser um objeto ou array JSON.');
            }
        }];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->safe()->except(['attributes']);
        $data['attributes'] = $this->filled('attributes')
            ? json_decode($this->string('attributes')->toString(), true, 512, JSON_THROW_ON_ERROR)
            : null;

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do produto.',
            'slug.required' => 'Informe o slug do produto.',
            'slug.regex' => 'Use apenas letras minúsculas, números e hífens no slug.',
            'slug.unique' => 'Este slug já está sendo usado por outro produto.',
            'category_id.required' => 'Selecione a categoria.',
            'brand_id.required' => 'Selecione a marca.',
            'gtin.regex' => 'Informe um GTIN com 8, 12, 13 ou 14 dígitos.',
            'attributes.json' => 'Informe um JSON válido nos atributos.',
            'canonical_image_url.url' => 'Informe uma URL HTTP ou HTTPS válida para a imagem.',
            'status.enum' => 'Selecione um status válido.',
            'merged_into_id.required_if' => 'Selecione o produto de destino para a mesclagem.',
            'merged_into_id.prohibited_unless' => 'O destino da mesclagem só pode ser usado quando o status for mesclado.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim($this->string('name')->toString());
        $slug = trim($this->string('slug')->toString());
        $status = $this->string('status')->toString();

        $this->merge([
            'name' => $name,
            'slug' => $slug !== '' ? $slug : Str::slug($name),
            'product_group_id' => $this->filled('product_group_id') ? $this->input('product_group_id') : null,
            'description' => $this->filled('description') ? trim($this->string('description')->toString()) : null,
            'gtin' => $this->filled('gtin') ? trim($this->string('gtin')->toString()) : null,
            'attributes' => $this->filled('attributes') ? trim($this->string('attributes')->toString()) : null,
            'canonical_image_url' => $this->filled('canonical_image_url') ? trim($this->string('canonical_image_url')->toString()) : null,
            'merged_into_id' => $status === ProductStatus::Merged->value && $this->filled('merged_into_id') ? $this->input('merged_into_id') : null,
        ]);
    }
}
