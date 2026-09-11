<?php

namespace App\Http\Requests\Admin;

use App\Domain\Catalog\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
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
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(Category::class, 'slug')->ignore($category),
            ],
            'parent_id' => ['nullable', 'integer', Rule::exists(Category::class, 'id')],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('parent_id')) {
                    return;
                }

                $category = $this->route('category');
                $parentId = $this->integer('parent_id');

                if (! $category instanceof Category || $parentId === 0) {
                    return;
                }

                if ($parentId === $category->getKey() || $this->parentChainContains($parentId, $category)) {
                    $validator->errors()->add('parent_id', 'A categoria pai não pode ser a própria categoria nem uma de suas descendentes.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome da categoria.',
            'slug.required' => 'Informe o slug da categoria.',
            'slug.regex' => 'Use apenas letras minúsculas, números e hífens no slug.',
            'slug.unique' => 'Este slug já está sendo usado por outra categoria.',
            'parent_id.exists' => 'A categoria pai selecionada não existe.',
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

    private function parentChainContains(int $parentId, Category $category): bool
    {
        $visited = [];
        $currentId = $parentId;

        while ($currentId !== 0 && ! isset($visited[$currentId])) {
            if ($currentId === $category->getKey()) {
                return true;
            }

            $visited[$currentId] = true;
            $currentId = (int) (Category::query()->whereKey($currentId)->value('parent_id') ?? 0);
        }

        return false;
    }
}
