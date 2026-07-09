<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CmsTag extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'name', 'slug', 'company_id', 'created_by', 'updated_by',
    ];

    // ── Relationships ──────────────────────────────────────
    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(CmsBlog::class, 'cms_blog_tags', 'tag_id', 'blog_id');
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
