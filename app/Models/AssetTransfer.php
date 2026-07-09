<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    protected $table = 'asset_transfers';

    protected $fillable = [
        'asset_id', 'transfer_number',
        'from_location_id', 'to_location_id',
        'from_department_id', 'to_department_id',
        'from_employee_id', 'to_employee_id',
        'transfer_date', 'reason', 'status', 'notes',
        'created_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
        ];
    }

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function fromLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'from_location_id'); }
    public function toLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'to_location_id'); }
    public function fromDepartment(): BelongsTo { return $this->belongsTo(Department::class, 'from_department_id'); }
    public function toDepartment(): BelongsTo { return $this->belongsTo(Department::class, 'to_department_id'); }
    public function fromEmployee(): BelongsTo { return $this->belongsTo(User::class, 'from_employee_id'); }
    public function toEmployee(): BelongsTo { return $this->belongsTo(User::class, 'to_employee_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
