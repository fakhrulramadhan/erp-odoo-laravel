<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountingPeriod;
use App\Models\CashAccount;
use App\Models\Currency;
use App\Models\FiscalYear;
use App\Models\Journal;
use App\Models\PaymentTerm;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;
        $currency = Currency::where('code', 'IDR')->firstOrFail();

        $rootAccounts = [
            ['code' => '1000', 'name' => 'Assets', 'account_type' => 'asset', 'normal_balance' => 'debit', 'journal_type' => 'general', 'is_group' => true],
            ['code' => '2000', 'name' => 'Liabilities', 'account_type' => 'liability', 'normal_balance' => 'credit', 'journal_type' => 'general', 'is_group' => true],
            ['code' => '3000', 'name' => 'Equity', 'account_type' => 'equity', 'normal_balance' => 'credit', 'journal_type' => 'general', 'is_group' => true],
            ['code' => '4000', 'name' => 'Revenue', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'journal_type' => 'sales', 'is_group' => true],
            ['code' => '5000', 'name' => 'Expenses', 'account_type' => 'expense', 'normal_balance' => 'debit', 'journal_type' => 'purchase', 'is_group' => true],
        ];

        foreach ($rootAccounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], array_merge($account, ['company_id' => $companyId]));
        }

        Account::firstOrCreate(['code' => '1010'], ['company_id' => $companyId, 'code' => '1010', 'name' => 'Cash', 'account_type' => 'asset', 'normal_balance' => 'debit', 'journal_type' => 'cash']);
        Account::firstOrCreate(['code' => '1020'], ['company_id' => $companyId, 'code' => '1020', 'name' => 'Bank', 'account_type' => 'asset', 'normal_balance' => 'debit', 'journal_type' => 'bank']);
        Account::firstOrCreate(['code' => '4010'], ['company_id' => $companyId, 'code' => '4010', 'name' => 'Sales Revenue', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'journal_type' => 'sales']);
        Account::firstOrCreate(['code' => '5010'], ['company_id' => $companyId, 'code' => '5010', 'name' => 'Cost of Goods Sold', 'account_type' => 'expense', 'normal_balance' => 'debit', 'journal_type' => 'purchase']);

        Tax::firstOrCreate(['company_id' => $companyId, 'code' => 'PPN'], [
            'company_id' => $companyId,
            'name' => 'PPN 11%',
            'code' => 'PPN',
            'rate' => 11,
            'type' => 'percentage',
            'is_inclusive' => false,
            'is_active' => true,
        ]);

        $fiscalYear = FiscalYear::firstOrCreate(['code' => 'FY2026'], [
            'company_id' => $companyId,
            'name' => 'Fiscal Year 2026',
            'code' => 'FY2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        AccountingPeriod::firstOrCreate(['code' => '202601'], [
            'fiscal_year_id' => $fiscalYear->id,
            'name' => 'January 2026',
            'code' => '202601',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'is_closed' => false,
        ]);

        PaymentTerm::firstOrCreate(['company_id' => $companyId, 'code' => 'NET30'], [
            'company_id' => $companyId,
            'name' => 'Net 30',
            'code' => 'NET30',
            'days' => 30,
            'description' => 'Payment due in 30 days',
            'is_active' => true,
        ]);

        Journal::firstOrCreate(['company_id' => $companyId, 'code' => 'GEN'], [
            'company_id' => $companyId,
            'code' => 'GEN',
            'name' => 'General Journal',
            'journal_type' => 'general',
            'description' => 'General accounting journal',
            'is_active' => true,
        ]);

        CashAccount::firstOrCreate(['company_id' => $companyId, 'code' => 'CASH-01'], [
            'company_id' => $companyId,
            'code' => 'CASH-01',
            'name' => 'Main Cash',
            'currency_id' => $currency->id,
            'opening_balance' => 0,
            'current_balance' => 0,
            'description' => 'Primary cash account',
            'is_active' => true,
        ]);
    }
}
