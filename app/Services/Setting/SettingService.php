<?php

namespace App\Services\Setting;

use App\Models\{Currency, TaxSetting, NumberingSequence, Branch, Department, Position};
use Illuminate\Support\Collection;

class SettingService
{
    // ─── Currencies ─────────────────────────────────

    public function currencies(?int $perPage = 15)
    {
        return Currency::orderBy('is_default', 'desc')->orderBy('name')->paginate($perPage);
    }

    public function createCurrency(array $data): Currency
    {
        if (!empty($data['is_default'])) {
            Currency::query()->update(['is_default' => false]);
        }
        return Currency::create($data);
    }

    public function updateCurrency(int $id, array $data): Currency
    {
        if (!empty($data['is_default'])) {
            Currency::query()->where('id', '!=', $id)->update(['is_default' => false]);
        }
        $currency = Currency::findOrFail($id);
        $currency->update($data);
        return $currency;
    }

    // ─── Tax Settings ───────────────────────────────

    public function taxSettings(int $companyId, ?int $perPage = 15)
    {
        return TaxSetting::where('company_id', $companyId)->paginate($perPage);
    }

    public function createTaxSetting(array $data): TaxSetting
    {
        return TaxSetting::create($data);
    }

    public function updateTaxSetting(int $id, array $data): TaxSetting
    {
        $tax = TaxSetting::findOrFail($id);
        $tax->update($data);
        return $tax;
    }

    // ─── Numbering Sequences ────────────────────────

    public function numberingSequences(int $companyId, ?int $perPage = 15)
    {
        return NumberingSequence::where('company_id', $companyId)->paginate($perPage);
    }

    public function createNumberingSequence(array $data): NumberingSequence
    {
        return NumberingSequence::create($data);
    }

    public function updateNumberingSequence(int $id, array $data): NumberingSequence
    {
        $seq = NumberingSequence::findOrFail($id);
        $seq->update($data);
        return $seq;
    }

    public function generateNumber(int $id): string
    {
        $seq = NumberingSequence::findOrFail($id);
        return $seq->generateNext();
    }

    // ─── Branches ───────────────────────────────────

    public function branches(int $companyId, ?int $perPage = 15)
    {
        return Branch::where('company_id', $companyId)->paginate($perPage);
    }

    // ─── Departments ────────────────────────────────

    public function departments(int $companyId, ?int $perPage = 15)
    {
        return Department::where('company_id', $companyId)
            ->with('parent', 'manager')
            ->paginate($perPage);
    }

    // ─── Positions ──────────────────────────────────

    public function positions(int $companyId, ?int $perPage = 15)
    {
        return Position::where('company_id', $companyId)
            ->with('department')
            ->paginate($perPage);
    }
}
