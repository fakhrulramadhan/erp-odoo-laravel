<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsMedia extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'file_name', 'file_path', 'file_type', 'file_size', 'alt_text',
        'folder', 'uploaded_by', 'company_id',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
