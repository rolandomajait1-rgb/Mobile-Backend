<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Draft extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'author_id',
        'author_name',
    ];

    protected $appends = ['featured_image_url'];

    /**
     * Get the featured image URL
     */
    public function getFeaturedImageUrlAttribute()
    {
        $value = $this->getRawOriginal('featured_image');
        
        if (!$value) {
            return 'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald';
        }

        $path = trim($value);
        if ($path === '') {
            return 'https://placehold.co/800x600/0891b2/ffffff?text=La+Verdad+Herald';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        return $baseUrl.'/storage/'.$path;
    }

    /**
     * Get the author (user) of the draft
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the categories for the draft
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'draft_category');
    }

    /**
     * Get the tags for the draft
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'draft_tag');
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($draft) {
            if (empty($draft->slug)) {
                $baseSlug = Str::slug($draft->title);
                $slug = $baseSlug;
                $counter = 1;

                while (Draft::where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $draft->slug = $slug;
            }
        });
    }
}