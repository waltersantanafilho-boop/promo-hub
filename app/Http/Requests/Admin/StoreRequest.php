<?php

namespace App\Http\Requests\Admin;

use App\Domain\Store\Store;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
        $store = $this->route('store');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(Store::class, 'slug')->ignore($store),
            ],
            'logo_url' => ['required', 'url:http,https', 'max:255'],
            'website_url' => ['required', 'url:http,https', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome da loja.',
            'slug.required' => 'Informe o slug da loja.',
            'slug.regex' => 'Use apenas letras minúsculas, números e hífens no slug.',
            'slug.unique' => 'Este slug já está sendo usado por outra loja.',
            'logo_url.required' => 'Informe a URL do logo.',
            'logo_url.url' => 'Informe uma URL HTTP ou HTTPS válida para o logo.',
            'website_url.required' => 'Informe a URL do site.',
            'website_url.url' => 'Informe uma URL HTTP ou HTTPS válida para o site.',
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
