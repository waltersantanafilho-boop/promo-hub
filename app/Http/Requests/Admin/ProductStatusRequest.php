<?php

namespace App\Http\Requests\Admin;

use App\Domain\Catalog\Enums\ProductStatus;
use App\Domain\Catalog\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductStatusRequest extends FormRequest
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
        return [
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
        }];
    }

    protected function prepareForValidation(): void
    {
        $status = $this->string('status')->toString();

        $this->merge([
            'merged_into_id' => $status === ProductStatus::Merged->value && $this->filled('merged_into_id') ? $this->input('merged_into_id') : null,
        ]);
    }
}
