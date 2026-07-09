<?php

namespace App\Http\Requests\Api\V1\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_type' => 'required|in:customer,vendor',
            'customer_id' => 'nullable|required_if:invoice_type,customer|exists:customers,id',
            'vendor_id' => 'nullable|required_if:invoice_type,vendor|exists:vendors,id',
            'currency_id' => 'required|exists:currencies,id',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
        ];
    }
}
