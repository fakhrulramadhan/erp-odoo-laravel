<?php

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage companies');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:30',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'tax_id' => 'nullable|string|max:50',
            'currency_id' => 'nullable|exists:currencies,id',
            'notes' => 'nullable|string',
        ];
    }
}
