<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleInteraction;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Article::with(['category', 'tags', 'author']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('content', 'like', '%'.$request->search.'%');
            });
        }

        $articles = $query->orderBy('published_at', 'desc')->paginate(15);

        return response()->json($articles);
    }

    /**
     * Public listing of published articles (no auth required)
     */
    public function publicIndex(Request $request)
    {
        // Sanitize cache key inputs
        $cacheParams = [
            'category_id' => $request->integer('category_id'),
            'search' => substr($request->string('search'), 0, 100),
            'latest' => $request->boolean('latest'),
            'limit' => min($request->integer('limit', 9), 50),
            'page' => $request->integer('page', 1),
        ];
        $cacheKey = 'articles_public_' . md5(json_encode($cacheParams));
        
        $articles = cache()->remember($cacheKey, 300, function() use ($request, $cacheParams) {
            $query = Article::select('id', 'title', 'slug', 'excerpt', 'featured_image', 'published_at', 'category_id', 'author_id', 'author_name')
                ->with([
                    'category:id,name,slug',
                    'tags:id,name,slug',
                ])
                ->where('status', 'published');

            if ($cacheParams['category_id']) {
                $query->where('category_id', $cacheParams['category_id']);
            }

            if ($cacheParams['search']) {
                $query->where(function($q) use ($cacheParams) {
                    $q->where('title', 'like', '%'.$cacheParams['search'].'%')
                      ->orWhere('excerpt', 'like', '%'.$cacheParams['search'].'%');
                });
            }

            if ($cacheParams['latest']) {
                return $query->orderBy('published_at', 'desc')->limit($cacheParams['limit'])->get();
            }

            return $query->orderBy('published_at', 'desc')->paginate(15);
        });

        return response()->json($articles);
    }

    /**
     * Public view of a single published article (no auth required)
     */
    public function publicShow(Request $request, $id)
    {
        $article = Article::with([
                'category:id,name,slug',
                'tags:id,name,slug',
            ])
            ->where('status', 'published')
            ->findOrFail($id);

        // Log the view asynchronously (don't wait for it)
        dispatch(function() use ($article, $request) {
            ArticleInteraction::create([
                'article_id' => $article->id,
                'user_id' => null,
                'type' => ArticleInteraction::TYPE_VIEWED,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        })->afterResponse();

        return response()->json($article);
    }

    /**
     * Get latest published articles
     */
    public function latestArticles(Request $request)
    {
        $limit = min($request->integer('limit', 10), 50); // Max 50
        
        $articles = cache()->remember('latest_articles_' . $limit, 300, function() use ($limit) {
            return Article::select('id', 'title', 'slug', 'excerpt', 'featured_image', 'published_at', 'category_id', 'author_name')
                ->with([
                    'category:id,name,slug',
                    'tags:id,name,slug',
                ])
                ->where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();
        });

        return response()->json($articles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:articles',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published,archived',
            'category_id' => 'required|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        // Handle image upload to Cloudinary
        $imageUrl = null;
        if ($request->hasFile('featured_image')) {
            try {
                if (function_exists('cloudinary')) {
                    $image = $request->file('featured_image');
                    $uploadedFile = cloudinary()->upload($image->getRealPath(), [
                        'folder' => 'articles',
                        'transformation' => [
                            'width' => 1200,
                            'height' => 630,
                            'crop' => 'limit'
                        ]
                    ]);
                    $imageUrl = $uploadedFile->getSecurePath();
                } else {
                    // Fallback: save to public storage
                    $path = $request->file('featured_image')->store('articles', 'public');
                    $imageUrl = '/storage/' . $path;
                }
            } catch (\Exception $e) {
                \Log::error('Image upload failed: ' . $e->getMessage());
                // Continue without image
            }
        }

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'featured_image' => $imageUrl,
            'status' => $validated['status'],
            'category_id' => $validated['category_id'],
            'author_id' => $request->user()->id,
            'author_name' => $request->user()->name,
        ]);

        if (isset($validated['tag_ids'])) {
            $article->tags()->sync($validated['tag_ids']);
        }

        return response()->json($article->load(['category', 'tags']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Article $article)
    {
        // Log the view
        ArticleInteraction::create([
            'article_id' => $article->id,
            'user_id' => $request->user()?->id,
            'type' => ArticleInteraction::TYPE_VIEWED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($article->load(['category', 'tags', 'author']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:articles,slug,'.$article->id,
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'sometimes|in:draft,published,archived',
            'category_id' => 'sometimes|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        // Handle image upload to Cloudinary
        if ($request->hasFile('featured_image')) {
            try {
                if (function_exists('cloudinary')) {
                    $image = $request->file('featured_image');
                    $uploadedFile = cloudinary()->upload($image->getRealPath(), [
                        'folder' => 'articles',
                        'transformation' => [
                            'width' => 1200,
                            'height' => 630,
                            'crop' => 'limit'
                        ]
                    ]);
                    $validated['featured_image'] = $uploadedFile->getSecurePath();
                } else {
                    // Fallback: save to public storage
                    $path = $request->file('featured_image')->store('articles', 'public');
                    $validated['featured_image'] = '/storage/' . $path;
                }
            } catch (\Exception $e) {
                \Log::error('Image upload failed: ' . $e->getMessage());
                // Keep existing image if upload fails
                unset($validated['featured_image']);
            }
        }

        $article->update($validated);

        if (isset($validated['tag_ids'])) {
            $article->tags()->sync($validated['tag_ids']);
        }

        return response()->json($article->load(['category', 'tags']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return response()->json(['message' => 'Article deleted successfully']);
    }

    /**
     * Like an article
     */
    public function like(Request $request, Article $article)
    {
        $existing = ArticleInteraction::where('article_id', $article->id)
            ->where('user_id', $request->user()->id)
            ->where('type', ArticleInteraction::TYPE_LIKED)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Article already liked'], 400);
        }

        ArticleInteraction::create([
            'article_id' => $article->id,
            'user_id' => $request->user()->id,
            'type' => ArticleInteraction::TYPE_LIKED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Article liked successfully']);
    }

    /**
     * Unlike an article
     */
    public function unlike(Request $request, Article $article)
    {
        ArticleInteraction::where('article_id', $article->id)
            ->where('user_id', $request->user()->id)
            ->where('type', ArticleInteraction::TYPE_LIKED)
            ->delete();

        return response()->json(['message' => 'Article unliked successfully']);
    }

    /**
     * Share an article
     */
    public function share(Request $request, Article $article)
    {
        ArticleInteraction::create([
            'article_id' => $article->id,
            'user_id' => $request->user()?->id,
            'type' => ArticleInteraction::TYPE_SHARED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Article share recorded']);
    }

    /**
     * Get the authenticated user's liked articles
     */
    public function likedArticles(Request $request)
    {
        $articles = Article::with(['category', 'tags', 'author'])
            ->whereHas('interactions', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                      ->where('type', ArticleInteraction::TYPE_LIKED);
            })
            ->orderBy('published_at', 'desc')
            ->get(); // Using get instead of paginate to make it easier for frontend if it wasn't expecting pagination

        return response()->json($articles);
    }

    /**
     * Get the authenticated user's shared articles
     */
    public function sharedArticles(Request $request)
    {
        $articles = Article::with(['category', 'tags', 'author'])
            ->whereHas('interactions', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                      ->where('type', ArticleInteraction::TYPE_SHARED);
            })
            ->orderBy('published_at', 'desc')
            ->get();

        return response()->json($articles);
    }
}