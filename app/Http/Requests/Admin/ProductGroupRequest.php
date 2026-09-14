<?php

namespace App\Http\Requests\Admin;

use App\Domain\Catalog\Brand;
use App\Domain\Catalog\Category;
use App\Domain\Catalog\ProductGroup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductGroupRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(ProductGroup::class, 'slug')->ignore($this->route('product_group')),
            ],
            'category_id' => ['required', 'integer', Rule::exists(Category::class, 'id')],
            'brand_id' => ['required', 'integer', Rule::exists(Brand::class, 'id')],
            'description' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do grupo de produtos.',
            'slug.required' => 'Informe o slug do grupo de produtos.',
            'slug.regex' => 'Use apenas letras minúsculas, números e hífens no slug.',
            'slug.unique' => 'Este slug já está sendo usado por outro grupo de produtos.',
            'category_id.required' => 'Selecione a categoria.',
            'category_id.exists' => 'A categoria selecionada não existe.',
            'brand_id.required' => 'Selecione a marca.',
            'brand_id.exists' => 'A marca selecionada não existe.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim($this->string('name')->toString());
        $slug = trim($this->string('slug')->toString());

        $this->merge([
            'name' => $name,
            'slug' => $slug !== '' ? $slug : Str::slug($name),
            'description' => $this->filled('description') ? trim($this->string('description')->toString()) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
