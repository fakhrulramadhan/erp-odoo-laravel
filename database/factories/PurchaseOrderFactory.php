<?php

namespace Database\Factories;

use App\Enums\PurchaseOrderStatus;
use App\Models\Vendor;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Warehouse;
use App\Models\Branch;
use App\Models\User;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-3 months', 'now');
        $status = fake()->randomElement(PurchaseOrderStatus::values());

        return [
            'order_number' => 'PO-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 6, '0', STR_PAD_LEFT),
            'vendor_id' => Vendor::factory(),
            'company_id' => Company::factory(),
            'currency_id' => Currency::factory(),
            'warehouse_id' => Warehouse::factory(),
            'branch_id' => Branch::factory(),
            'status' => $status,
            'order_date' => $date,
            'expected_date' => (clone $date) . '+30 days',
            'notes' => $this->faker->optional(0.3)->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
