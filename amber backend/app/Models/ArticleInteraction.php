<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'user_id',
        'type',
        'ip_address',
        'user_agent',
    ];

    public const TYPE_LIKED = 'liked';
    public const TYPE_SHARED = 'shared';
    public const TYPE_VIEWED = 'viewed';

    /**
     * Get the article that was interacted with
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the user who interacted (if logged in)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
