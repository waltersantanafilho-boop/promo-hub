<?php

namespace App\Http\Requests\Admin;

use App\Domain\Store\Enums\StoreSourceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use JsonException;

class StoreSourceRequest extends FormRequest
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
            'type' => ['required', Rule::enum(StoreSourceType::class)],
            'provider_key' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9][a-z0-9._-]*$/'],
            'config' => ['nullable', 'string', 'json', 'max:50000'],
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
                if ($validator->errors()->has('config') || ! $this->filled('config')) {
                    return;
                }

                try {
                    $config = json_decode($this->string('config')->toString(), true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    return;
                }

                if (! is_array($config)) {
                    $validator->errors()->add('config', 'A configuração deve ser um objeto ou array JSON.');

                    return;
                }

                if ($this->containsSensitiveKey($config)) {
                    $validator->errors()->add('config', 'Não inclua secrets, tokens, senhas ou credenciais na configuração.');
                }
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->safe()->only(['type', 'provider_key', 'is_active']);
        $data['config'] = $this->filled('config')
            ? json_decode($this->string('config')->toString(), true, 512, JSON_THROW_ON_ERROR)
            : null;

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Selecione o tipo da fonte.',
            'type.enum' => 'Selecione um tipo de fonte válido.',
            'provider_key.regex' => 'Use apenas letras minúsculas, números, pontos, hífens e sublinhados na chave do provider.',
            'config.json' => 'Informe um JSON válido na configuração.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provider_key' => $this->filled('provider_key') ? trim($this->string('provider_key')->toString()) : null,
            'config' => $this->filled('config') ? trim($this->string('config')->toString()) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @param  array<mixed>  $config
     */
    private function containsSensitiveKey(array $config): bool
    {
        foreach ($config as $key => $value) {
            $normalizedKey = is_string($key) ? Str::snake($key) : '';

            if (preg_match('/(?:^|_)(secret|token|password|credential|authorization|bearer|api_key|access_key|client_id|client_secret|private_key)(?:$|_)/', $normalizedKey) === 1) {
                return true;
            }

            if (is_array($value) && $this->containsSensitiveKey($value)) {
                return true;
            }
        }

        return false;
    }
}
