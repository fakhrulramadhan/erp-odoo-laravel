<?php

namespace Database\Seeders;

use App\Models\{User, Company, Currency, Branch};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed roles & permissions
        $this->call(RolePermissionSeeder::class);

        // 2. Seed default currencies
        $idr = Currency::firstOrCreate(['code' => 'IDR'], [
            'name' => 'Indonesian Rupiah',
            'symbol' => 'Rp',
            'decimal_places' => 0,
            'exchange_rate' => 1.0,
            'is_default' => true,
            'is_active' => true,
        ]);

        $usd = Currency::firstOrCreate(['code' => 'USD'], [
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimal_places' => 2,
            'exchange_rate' => 15900.0,
            'is_default' => false,
            'is_active' => true,
        ]);

        // 3. Seed default company
        $company = Company::firstOrCreate(['name' => 'PT Demo ERP'], [
            'legal_name' => 'PT Demo ERP Indonesia',
            'email' => 'admin@demo-erp.com',
            'phone' => '+62 21 1234567',
            'address' => 'Jl. Sudirman No. 1',
            'city' => 'Jakarta',
            'country' => 'ID',
            'currency_id' => $idr->id,
            'is_active' => true,
        ]);

        // 4. Seed main branch
        $branch = Branch::firstOrCreate(
            ['code' => 'HO', 'company_id' => $company->id],
            [
                'name' => 'Head Office',
                'address' => 'Jl. Sudirman No. 1, Jakarta',
                'is_main' => true,
                'is_active' => true,
            ]
        );

        // 5. Seed super-admin user
        $admin = User::firstOrCreate(['email' => 'admin@demo-erp.com'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);
        $admin->assignRole('super-admin');

        // 6. Seed Master Data samples
        $this->call([
            MasterDataSeeder::class,
            PurchaseInventorySeeder::class,
            FinanceSeeder::class,
            EnterpriseSeeder::class,
        ]);
    }
}
