<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_type' => $this->invoice_type,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'balance_amount' => $this->balance_amount,
            'due_date' => $this->due_date?->toDateString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
