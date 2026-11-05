<?php

namespace Tests\Unit;

use App\Models\PurchaseOrder;
use App\Repositories\PurchaseOrderRepository;
use App\Services\Purchasing\PurchaseOrderService;
use Tests\TestCase;

class PurchaseOrderLineMappingTest extends TestCase
{
    private function normalizeLine(array $line): array
    {
        $service = new PurchaseOrderService(new PurchaseOrderRepository());
        $method = new \ReflectionMethod($service, 'normalizeLine');
        $method->setAccessible(true);

        return $method->invoke($service, $line);
    }

    public function test_api_field_names_map_to_table_columns(): void
    {
        $fields = $this->normalizeLine([
            'product_id' => 1,
            'quantity' => 2,
            'unit_price' => 1500,
            'discount_rate' => 10,
            'tax_rate' => 11,
            'description' => 'Test line',
        ]);

        $this->assertEquals(1500.0, $fields['price']);
        $this->assertEquals(10.0, $fields['discount_percent']);
        $this->assertEquals(3000.0, $fields['subtotal']);
        $this->assertEquals(300.0, $fields['discount_amount']);
        $this->assertEquals(297.0, $fields['tax_amount']);
        $this->assertEquals(2997.0, $fields['total']);
    }

    public function test_price_column_name_is_accepted_as_fallback(): void
    {
        $fields = $this->normalizeLine([
            'product_id' => 1,
            'quantity' => 1,
            'price' => 500,
        ]);

        $this->assertEquals(500.0, $fields['price']);
        $this->assertEquals(500.0, $fields['total']);
    }

    public function test_invoice_relation_uses_source_columns(): void
    {
        $relation = (new PurchaseOrder())->invoice();

        $this->assertSame('source_id', $relation->getForeignKeyName());
        $this->assertSame('id', $relation->getLocalKeyName());
    }
}
