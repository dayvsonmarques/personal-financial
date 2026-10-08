<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\Intl\Currencies;

class UpdateOrganizationRequest extends FormRequest
{
    /** Locales suportados (RNF-07: apenas pt-BR no MVP). */
    public const LOCALES = ['pt-BR'];

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'currency' => ['required', 'string', Rule::in(Currencies::getCurrencyCodes())],
            'timezone' => ['required', 'string', 'timezone:all'],
            'locale' => ['required', 'string', Rule::in(self::LOCALES)],
        ];
    }
}
