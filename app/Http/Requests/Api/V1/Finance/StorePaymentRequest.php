<?php

namespace App\Http\Requests\Api\V1\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_type' => 'required|in:customer,vendor',
            'customer_id' => 'nullable|required_if:payment_type,customer|exists:customers,id',
            'vendor_id' => 'nullable|required_if:payment_type,vendor|exists:vendors,id',
            'currency_id' => 'required|exists:currencies,id',
            'payment_method' => 'required|in:cash,bank_transfer,e_wallet',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'required|exists:invoices,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ];
    }
}
