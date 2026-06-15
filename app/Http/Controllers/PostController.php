<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Display a listing of the resource (paginated).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $posts = $this->postService->getPaginatedPosts($perPage, $search);
        
        return PostResource::collection($posts);
    }

    /**
     * Display a listing of trending posts.
     */
    public function trending(Request $request)
    {
        $limit = $request->query('limit', 5);
        $posts = $this->postService->getTrendingPosts($limit);
        
        return PostResource::collection($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePostRequest $request)
    {
        $guest = $request->user();
        $mediaFile = $request->file('media');
        
        $post = $this->postService->createPost($request->only('content'), $guest, $mediaFile);
        
        return new PostResource($post);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = $this->postService->getPostById($id);
        if (!$post) {
            return response()->json(['error' => 'Post not found.'], 404);
        }
        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $guest = $request->user();
        
        // Authorization: only owners or admins can update
        if ($post->guest_id !== $guest->id && !$guest->is_admin) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $updatedPost = $this->postService->updatePost($post, $request->validated());
        
        return new PostResource($updatedPost);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post)
    {
        $guest = $request->user();

        // Authorization: only owners or admins can delete
        if ($post->guest_id !== $guest->id && !$guest->is_admin) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $this->postService->deletePost($post);

        return response()->json(['message' => 'Post deleted successfully.']);
    }

    /**
     * Toggle like/unlike on a post.
     */
    public function toggleLike(Request $request, Post $post)
    {
        $guest = $request->user();
        $isLiked = $this->postService->toggleLikePost($post, $guest);

        return response()->json([
            'liked' => $isLiked,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }
}
