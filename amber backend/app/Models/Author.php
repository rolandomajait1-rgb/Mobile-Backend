<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'website',
        'social_links',
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->user?->name ?? 'Unknown Author';
    }

    protected $casts = [
        'social_links' => 'array',
    ];

    /**
     * Get the user that owns the author profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all articles by this author
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Get all drafts by this author
     */
    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class);
    }
}
