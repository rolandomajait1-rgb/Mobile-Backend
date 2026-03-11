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
        $query = Article::with(['category', 'tags', 'author'])
            ->where('status', 'published');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('content', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->has('latest') && $request->latest) {
            $limit = $request->get('limit', 9);
            $articles = $query->orderBy('published_at', 'desc')->limit($limit)->get();
            return response()->json($articles);
        }

        $articles = $query->orderBy('published_at', 'desc')->paginate(15);

        return response()->json($articles);
    }

    /**
     * Public view of a single published article (no auth required)
     */
    public function publicShow(Request $request, $id)
    {
        $article = Article::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->findOrFail($id);

        // Log the view (without user_id for public)
        ArticleInteraction::create([
            'article_id' => $article->id,
            'user_id' => null,
            'type' => ArticleInteraction::TYPE_VIEWED,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($article);
    }

    /**
     * Get latest published articles
     */
    public function latestArticles(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $articles = Article::with(['category', 'tags', 'author'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

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
            } catch (\Exception $e) {
                \Log::error('Cloudinary upload failed: ' . $e->getMessage());
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
            } catch (\Exception $e) {
                \Log::error('Cloudinary upload failed: ' . $e->getMessage());
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
}