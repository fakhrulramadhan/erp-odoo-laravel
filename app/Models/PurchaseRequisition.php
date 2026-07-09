<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'requisition_number', 'company_id', 'branch_id', 'department_id',
        'currency_id', 'status', 'requisition_date', 'required_date',
        'purpose', 'notes',
        'approved_by', 'approved_at', 'purchase_order_id',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'requisition_date' => 'date',
            'required_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function lines(): HasMany { return $this->hasMany(PurchaseRequisitionLine::class)->orderBy('line_number'); }

    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeByCompany($query, int $companyId) { return $query->where('company_id', $companyId); }
}
