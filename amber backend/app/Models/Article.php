<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    public const STATUS_PUBLISHED = 'published';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'author_id',
        'author_name',
        'category_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = ['featured_image_url'];

    /**
     * Get the likes count attribute
     */
    public function getLikesCountAttribute()
    {
        if (array_key_exists('likes_count', $this->attributes)) {
            return $this->attributes['likes_count'];
        }
        return $this->interactions()->where('type', 'liked')->count();
    }

    /**
     * Get the is_liked attribute
     */
    public function getIsLikedAttribute()
    {
        if (array_key_exists('is_liked', $this->attributes)) {
            return (bool) $this->attributes['is_liked'];
        }
        if (Auth::check()) {
            return $this->interactions()->where('user_id', Auth::id())->where('type', 'liked')->exists();
        }
        return false;
    }

    /**
     * Get the featured image URL
     */
    public function getFeaturedImageUrlAttribute()
    {
        return $this->resolveFeaturedImageUrl($this->getRawOriginal('featured_image'));
    }

    /**
     * Get the featured image attribute
     */
    public function getFeaturedImageAttribute($value)
    {
        return $this->resolveFeaturedImageUrl($value);
    }

    /**
     * Resolve the featured image URL
     */
    private function resolveFeaturedImageUrl(?string $value): string
    {
        if (!$value) {
            return 'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald';
        }

        $path = trim($value);
        if ($path === '') {
            return 'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald';
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        $appStoragePrefix = $appUrl !== '' ? $appUrl.'/storage/' : null;

        // Keep Cloudinary/external URLs as-is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if ($appStoragePrefix === null || !str_starts_with($path, $appStoragePrefix)) {
                return $path;
            }
            $path = '/storage/'.ltrim(substr($path, strlen($appStoragePrefix)), '/');
        }

        // Handle local storage paths
        if (str_starts_with($path, '/storage/')) {
            $relativePath = ltrim(substr($path, strlen('/storage/')), '/');
        } elseif (str_starts_with($path, 'storage/')) {
            $relativePath = ltrim(substr($path, strlen('storage/')), '/');
        } else {
            $relativePath = ltrim($path, '/');
        }

        if (str_starts_with($relativePath, 'public/')) {
            $relativePath = ltrim(substr($relativePath, strlen('public/')), '/');
        }

        if ($relativePath === '') {
            return 'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald';
        }

        // Check if file exists
        try {
            if (!Storage::disk('public')->exists($relativePath)) {
                return 'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald';
            }
        } catch (\Throwable $e) {
            return 'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald';
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $url = $baseUrl.'/storage/'.$relativePath;

        if (config('app.env') !== 'local' && str_starts_with($url, 'http://')) {
            return 'https://'.substr($url, strlen('http://'));
        }

        return $url;
    }

    /**
     * Get the author (user) of the article
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the categories for the article
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }

    /**
     * Get the tags for the article
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    /**
     * Get the article interactions (likes, shares)
     */
    public function interactions()
    {
        return $this->hasMany(ArticleInteraction::class);
    }

    /**
     * Get the category of the article
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope to get only published articles
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope to get only draft articles
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope to get only archived articles
     */
    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $baseSlug = Str::slug($article->title);
                $slug = $baseSlug;
                $counter = 1;

                while (Article::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $article->slug = $slug;
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }
}