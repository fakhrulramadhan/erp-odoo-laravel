<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsBlog extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'featured_image',
        'category_id', 'author_id', 'meta_title', 'meta_description',
        'status', 'published_at', 'company_id',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(CmsCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(CmsTag::class, 'cms_blog_tags', 'blog_id', 'tag_id');
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
