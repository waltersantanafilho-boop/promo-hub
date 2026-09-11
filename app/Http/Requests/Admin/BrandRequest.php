<?php

namespace App\Http\Requests\Admin;

use App\Domain\Catalog\Brand;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
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
        $brand = $this->route('brand');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(Brand::class, 'slug')->ignore($brand),
            ],
            'logo_url' => ['nullable', 'url:http,https', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome da marca.',
            'slug.required' => 'Informe o slug da marca.',
            'slug.regex' => 'Use apenas letras minúsculas, números e hífens no slug.',
            'slug.unique' => 'Este slug já está sendo usado por outra marca.',
            'logo_url.url' => 'Informe uma URL HTTP ou HTTPS válida para o logo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim($this->string('name')->toString());
        $slug = trim($this->string('slug')->toString());

        $this->merge([
            'name' => $name,
            'slug' => $slug !== '' ? $slug : Str::slug($name),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
