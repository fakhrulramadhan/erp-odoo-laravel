<?php

namespace Database\Seeders;

use App\Models\{
    ProductCategory,
    UnitOfMeasure,
    Product,
    Customer,
    Vendor,
    Warehouse,
    StockLocation,
    BankAccount,
    PaymentMethod,
    ChartOfAccount,
};
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedChartOfAccounts();
        $this->seedProductCategories();
        $this->seedUnitOfMeasures();
        $this->seedProducts();
        $this->seedCustomers();
        $this->seedVendors();
        $this->seedWarehouses();
        $this->seedStockLocations();
        $this->seedBankAccounts();
        $this->seedPaymentMethods();
    }

    // ─── Chart of Accounts ──────────────────────────

    private function seedChartOfAccounts(): void
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Assets', 'type' => 'asset', 'normal_balance' => 'debit', 'is_group' => true],
            ['code' => '1100', 'name' => 'Current Assets', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1000', 'is_group' => true],
            ['code' => '1110', 'name' => 'Cash', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1100'],
            ['code' => '1120', 'name' => 'Bank', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1100'],
            ['code' => '1130', 'name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1100'],
            ['code' => '1140', 'name' => 'Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1100'],
            ['code' => '1200', 'name' => 'Fixed Assets', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1000', 'is_group' => true],
            ['code' => '1210', 'name' => 'Equipment', 'type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1200'],

            // Liabilities
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'liability', 'normal_balance' => 'credit', 'is_group' => true],
            ['code' => '2100', 'name' => 'Current Liabilities', 'type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2000', 'is_group' => true],
            ['code' => '2110', 'name' => 'Accounts Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2100'],
            ['code' => '2120', 'name' => 'Tax Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2100'],

            // Equity
            ['code' => '3000', 'name' => 'Equity', 'type' => 'equity', 'normal_balance' => 'credit', 'is_group' => true],
            ['code' => '3100', 'name' => 'Owner\'s Equity', 'type' => 'equity', 'normal_balance' => 'credit', 'parent_code' => '3000'],
            ['code' => '3200', 'name' => 'Retained Earnings', 'type' => 'equity', 'normal_balance' => 'credit', 'parent_code' => '3000'],

            // Income
            ['code' => '4000', 'name' => 'Income', 'type' => 'income', 'normal_balance' => 'credit', 'is_group' => true],
            ['code' => '4100', 'name' => 'Sales Revenue', 'type' => 'income', 'normal_balance' => 'credit', 'parent_code' => '4000'],
            ['code' => '4200', 'name' => 'Other Income', 'type' => 'income', 'normal_balance' => 'credit', 'parent_code' => '4000'],

            // Expenses
            ['code' => '5000', 'name' => 'Expenses', 'type' => 'expense', 'normal_balance' => 'debit', 'is_group' => true],
            ['code' => '5100', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000'],
            ['code' => '5200', 'name' => 'Salary Expense', 'type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000'],
            ['code' => '5300', 'name' => 'Rent Expense', 'type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000'],
            ['code' => '5400', 'name' => 'Utilities Expense', 'type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000'],
        ];

        // First pass: create all accounts
        foreach ($accounts as $account) {
            $parentCode = $account['parent_code'] ?? null;
            unset($account['parent_code']);

            ChartOfAccount::create(array_merge($account, [
                'parent_id' => null,
                'level'     => 0,
                'path'      => null,
            ]));
        }

        // Second pass: set parent relationships
        foreach ($accounts as $account) {
            if (!empty($account['parent_code'])) {
                $child  = ChartOfAccount::where('code', $account['code'])->first();
                $parent = ChartOfAccount::where('code', $account['parent_code'])->first();
                if ($child && $parent) {
                    $child->update([
                        'parent_id' => $parent->id,
                        'level'     => $parent->level + 1,
                        'path'      => ($parent->path ? $parent->path . '/' : '') . $parent->id,
                    ]);
                }
            }
        }
    }

    // ─── Product Categories ─────────────────────────

    private function seedProductCategories(): void
    {
        $categories = [
            ['code' => 'ELEC', 'name' => 'Electronics'],
            ['code' => 'FOOD', 'name' => 'Food & Beverages'],
            ['code' => 'CLTH', 'name' => 'Clothing'],
            ['code' => 'OFFC', 'name' => 'Office Supplies'],
            ['code' => 'FURN', 'name' => 'Furniture'],
        ];

        foreach ($categories as $cat) {
            ProductCategory::create($cat);
        }

        // Sub-categories
        $elec = ProductCategory::where('code', 'ELEC')->first();
        $subcats = [
            ['code' => 'ELEC-MOB', 'name' => 'Mobile Phones', 'parent_id' => $elec->id],
            ['code' => 'ELEC-LAP', 'name' => 'Laptops', 'parent_id' => $elec->id],
            ['code' => 'ELEC-ACC', 'name' => 'Accessories', 'parent_id' => $elec->id],
        ];

        foreach ($subcats as $sub) {
            ProductCategory::create(array_merge($sub, ['level' => 1, 'path' => $elec->id]));
        }
    }

    // ─── Unit of Measures ───────────────────────────

    private function seedUnitOfMeasures(): void
    {
        $uoms = [
            ['code' => 'PCS',  'name' => 'Pieces',  'type' => 'unit'],
            ['code' => 'BOX',  'name' => 'Box',     'type' => 'unit'],
            ['code' => 'KG',   'name' => 'Kilogram', 'type' => 'weight'],
            ['code' => 'GRM',  'name' => 'Gram',     'type' => 'weight'],
            ['code' => 'LTR',  'name' => 'Liter',    'type' => 'volume'],
            ['code' => 'ML',   'name' => 'Milliliter', 'type' => 'volume'],
            ['code' => 'MTR',  'name' => 'Meter',    'type' => 'length'],
            ['code' => 'CM',   'name' => 'Centimeter', 'type' => 'length'],
            ['code' => 'SQM',  'name' => 'Square Meter', 'type' => 'area'],
            ['code' => 'SET',  'name' => 'Set',      'type' => 'unit'],
            ['code' => 'UNIT', 'name' => 'Unit',     'type' => 'unit'],
            ['code' => 'HR',   'name' => 'Hour',     'type' => 'unit'],
        ];

        foreach ($uoms as $uom) {
            UnitOfMeasure::create($uom);
        }
    }

    // ─── Products ───────────────────────────────────

    private function seedProducts(): void
    {
        $catElectronics = ProductCategory::where('code', 'ELEC')->first();
        $catMobile      = ProductCategory::where('code', 'ELEC-MOB')->first();
        $catLaptop      = ProductCategory::where('code', 'ELEC-LAP')->first();
        $uomPcs         = UnitOfMeasure::where('code', 'PCS')->first();
        $uomBox         = UnitOfMeasure::where('code', 'BOX')->first();

        $products = [
            [
                'code' => 'PRD-001', 'name' => 'Samsung Galaxy S24', 'barcode' => '8801234567890',
                'category_id' => $catMobile->id, 'uom_id' => $uomPcs->id, 'type' => 'stockable',
                'purchase_price' => 8000000, 'sales_price' => 12000000, 'minimum_stock' => 5,
            ],
            [
                'code' => 'PRD-002', 'name' => 'iPhone 15 Pro', 'barcode' => '8801234567891',
                'category_id' => $catMobile->id, 'uom_id' => $uomPcs->id, 'type' => 'stockable',
                'purchase_price' => 15000000, 'sales_price' => 20000000, 'minimum_stock' => 3,
            ],
            [
                'code' => 'PRD-003', 'name' => 'MacBook Pro M3', 'barcode' => '8801234567892',
                'category_id' => $catLaptop->id, 'uom_id' => $uomPcs->id, 'type' => 'stockable',
                'purchase_price' => 25000000, 'sales_price' => 32000000, 'minimum_stock' => 2,
            ],
            [
                'code' => 'PRD-004', 'name' => 'ThinkPad X1 Carbon', 'barcode' => '8801234567893',
                'category_id' => $catLaptop->id, 'uom_id' => $uomPcs->id, 'type' => 'stockable',
                'purchase_price' => 18000000, 'sales_price' => 24000000, 'minimum_stock' => 3,
            ],
            [
                'code' => 'PRD-005', 'name' => 'USB-C Cable', 'barcode' => '8801234567894',
                'category_id' => $catElectronics->id, 'uom_id' => $uomPcs->id, 'type' => 'stockable',
                'purchase_price' => 25000, 'sales_price' => 50000, 'minimum_stock' => 100,
            ],
            [
                'code' => 'PRD-006', 'name' => 'IT Consulting Service', 'barcode' => null,
                'category_id' => null, 'uom_id' => $uomPcs->id, 'type' => 'service',
                'purchase_price' => 0, 'sales_price' => 500000, 'minimum_stock' => 0,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }

    // ─── Customers ──────────────────────────────────

    private function seedCustomers(): void
    {
        $customers = [
            [
                'code' => 'CUST-001', 'name' => 'PT Maju Bersama', 'type' => 'company',
                'email' => 'info@majubersama.co.id', 'phone' => '021-5551234',
                'billing_address' => 'Jl. Sudirman No. 123', 'city' => 'Jakarta', 'state' => 'DKI Jakarta',
                'zip_code' => '12190', 'country' => 'ID', 'tax_number' => '01.234.567.8-012.000',
                'contact_person' => 'Budi Santoso', 'payment_term' => 'net_30', 'credit_limit' => 500000000,
            ],
            [
                'code' => 'CUST-002', 'name' => 'CV Sejahtera', 'type' => 'company',
                'email' => 'admin@sejahtera.co.id', 'phone' => '021-5555678',
                'billing_address' => 'Jl. Gatot Subroto No. 456', 'city' => 'Jakarta', 'state' => 'DKI Jakarta',
                'zip_code' => '12930', 'country' => 'ID', 'tax_number' => '02.345.678.9-013.000',
                'contact_person' => 'Andi Wijaya', 'payment_term' => 'net_60', 'credit_limit' => 300000000,
            ],
            [
                'code' => 'CUST-003', 'name' => 'John Doe', 'type' => 'individual',
                'email' => 'john.doe@email.com', 'phone' => '0812-3456-7890',
                'billing_address' => 'Jl. Diponegoro No. 789', 'city' => 'Bandung', 'state' => 'Jawa Barat',
                'zip_code' => '40115', 'country' => 'ID',
                'payment_term' => 'prepaid', 'credit_limit' => 10000000,
            ],
            [
                'code' => 'CUST-004', 'name' => 'PT Global Teknologi', 'type' => 'company',
                'email' => 'procurement@globaltek.co.id', 'phone' => '031-5559012',
                'billing_address' => 'Jl. Pemuda No. 321', 'city' => 'Surabaya', 'state' => 'Jawa Timur',
                'zip_code' => '60271', 'country' => 'ID',
                'contact_person' => 'Dewi Lestari', 'payment_term' => 'net_30', 'credit_limit' => 750000000,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }

    // ─── Vendors ────────────────────────────────────

    private function seedVendors(): void
    {
        $vendors = [
            [
                'code' => 'VND-001', 'name' => 'PT Samsung Electronics Indonesia',
                'email' => 'sales@samsung-id.com', 'phone' => '021-8881234',
                'address' => 'Jl. TB Simatupang No. 1', 'city' => 'Jakarta', 'state' => 'DKI Jakarta',
                'zip_code' => '12310', 'country' => 'ID', 'tax_number' => '01.234.567.8-011.000',
                'contact_person' => 'Kim Min Joon', 'payment_term' => 'net_30',
                'bank_name' => 'Bank BCA', 'bank_account_number' => '1234567890', 'bank_account_name' => 'PT Samsung Electronics',
            ],
            [
                'code' => 'VND-002', 'name' => 'PT Apple Indonesia',
                'email' => 'distribution@apple-id.com', 'phone' => '021-8885678',
                'address' => 'Jl. HR Rasuna Said No. 10', 'city' => 'Jakarta', 'state' => 'DKI Jakarta',
                'zip_code' => '12940', 'country' => 'ID',
                'contact_person' => 'Lisa Chen', 'payment_term' => 'net_60',
                'bank_name' => 'Bank Mandiri', 'bank_account_number' => '0987654321', 'bank_account_name' => 'PT Apple Indonesia',
            ],
            [
                'code' => 'VND-003', 'name' => 'Lenovo Indonesia',
                'email' => 'supply@lenovo-id.com', 'phone' => '021-8889012',
                'address' => 'Jl. Casablanca No. 88', 'city' => 'Jakarta', 'state' => 'DKI Jakarta',
                'zip_code' => '12870', 'country' => 'ID',
                'contact_person' => 'David Putra', 'payment_term' => 'net_30',
                'bank_name' => 'Bank BNI', 'bank_account_number' => '1122334455', 'bank_account_name' => 'Lenovo Indonesia',
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }
    }

    // ─── Warehouses ─────────────────────────────────

    private function seedWarehouses(): void
    {
        $warehouses = [
            ['code' => 'WH-JKT', 'name' => 'Jakarta Main Warehouse', 'address' => 'Jl. Pergudangan No. 1', 'city' => 'Jakarta', 'phone' => '021-7771234', 'is_main' => true],
            ['code' => 'WH-BDG', 'name' => 'Bandung Warehouse', 'address' => 'Jl. Soekarno-Hatta No. 100', 'city' => 'Bandung', 'phone' => '022-7775678'],
            ['code' => 'WH-SBY', 'name' => 'Surabaya Warehouse', 'address' => 'Jl. Ahmad Yani No. 200', 'city' => 'Surabaya', 'phone' => '031-7779012'],
        ];

        foreach ($warehouses as $wh) {
            Warehouse::create($wh);
        }
    }

    // ─── Stock Locations ────────────────────────────

    private function seedStockLocations(): void
    {
        $locations = [
            ['warehouse_code' => 'WH-JKT', 'code' => 'STOCK', 'name' => 'Stock', 'type' => 'internal'],
            ['warehouse_code' => 'WH-JKT', 'code' => 'RECV', 'name' => 'Receiving', 'type' => 'internal'],
            ['warehouse_code' => 'WH-JKT', 'code' => 'SHIP', 'name' => 'Shipping', 'type' => 'internal'],
            ['warehouse_code' => 'WH-JKT', 'code' => 'SCRAP', 'name' => 'Scrap Area', 'type' => 'scrap'],
            ['warehouse_code' => 'WH-BDG', 'code' => 'STOCK', 'name' => 'Stock', 'type' => 'internal'],
            ['warehouse_code' => 'WH-SBY', 'code' => 'STOCK', 'name' => 'Stock', 'type' => 'internal'],
            ['warehouse_code' => 'WH-SBY', 'code' => 'QC', 'name' => 'Quality Check', 'type' => 'internal'],
        ];

        foreach ($locations as $loc) {
            $warehouse = Warehouse::where('code', $loc['warehouse_code'])->first();
            StockLocation::create([
                'warehouse_id' => $warehouse->id,
                'code'         => $loc['code'],
                'name'         => $loc['name'],
                'type'         => $loc['type'],
            ]);
        }
    }

    // ─── Bank Accounts ──────────────────────────────

    private function seedBankAccounts(): void
    {
        $idr = \App\Models\Currency::where('code', 'IDR')->first();

        $accounts = [
            [
                'code' => 'CASH-001', 'name' => 'Petty Cash', 'type' => 'cash',
                'currency_id' => $idr->id, 'opening_balance' => 5000000, 'current_balance' => 5000000,
            ],
            [
                'code' => 'BANK-BCA', 'name' => 'BCA Operasional', 'type' => 'bank',
                'bank_name' => 'Bank BCA', 'account_number' => '1234567890', 'account_name' => 'PT ERP Demo',
                'currency_id' => $idr->id, 'opening_balance' => 500000000, 'current_balance' => 500000000,
            ],
            [
                'code' => 'BANK-MANDIRI', 'name' => 'Mandiri Tabungan', 'type' => 'bank',
                'bank_name' => 'Bank Mandiri', 'account_number' => '0987654321', 'account_name' => 'PT ERP Demo',
                'currency_id' => $idr->id, 'opening_balance' => 200000000, 'current_balance' => 200000000,
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::create($account);
        }
    }

    // ─── Payment Methods ────────────────────────────

    private function seedPaymentMethods(): void
    {
        $cashAccount = BankAccount::where('code', 'CASH-001')->first();
        $bcaAccount  = BankAccount::where('code', 'BANK-BCA')->first();

        $methods = [
            ['code' => 'PM-CASH',  'name' => 'Cash',           'type' => 'cash',          'bank_account_id' => $cashAccount->id],
            ['code' => 'PM-BCA',   'name' => 'BCA Transfer',   'type' => 'bank_transfer', 'bank_account_id' => $bcaAccount->id],
            ['code' => 'PM-QRIS',  'name' => 'QRIS',           'type' => 'qris'],
            ['code' => 'PM-DEBIT', 'name' => 'Debit Card',     'type' => 'debit'],
            ['code' => 'PM-CC',    'name' => 'Credit Card',    'type' => 'credit_card'],
            ['code' => 'PM-EWAL',  'name' => 'E-Wallet (OVO/GoPay/Dana)', 'type' => 'ewallet'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
}
