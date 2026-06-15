<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyAdminRequest;
use App\Http\Resources\PostResource;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Models\Comment;
use App\Services\PostService;
use App\Services\CommentService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected PostService $postService;
    protected CommentService $commentService;

    public function __construct(PostService $postService, CommentService $commentService)
    {
        $this->postService = $postService;
        $this->commentService = $commentService;
    }

    /**
     * Authenticate and promote current guest to admin.
     */
    public function auth(VerifyAdminRequest $request)
    {
        $secretKey = $request->validated()['secret_key'];
        $expectedKey = env('ADMIN_SECRET_KEY', 'tufu@123');

        if ($secretKey !== $expectedKey) {
            return response()->json([
                'error' => 'Invalid admin secret key.'
            ], 422);
        }

        $guest = $request->user();
        $guest->update(['is_admin' => true]);

        return response()->json([
            'message' => 'Successfully promoted to admin.',
            'is_admin' => true,
        ]);
    }

    /**
     * Get system-wide statistics for the dashboard.
     */
    public function stats(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        return response()->json([
            'stats' => [
                'total_guests' => \App\Models\Guest::count(),
                'total_posts' => \App\Models\Post::count(),
                'total_comments' => \App\Models\Comment::count(),
                'total_likes' => \App\Models\Like::count(),
            ]
        ]);
    }

    /**
     * Get all posts for admin management (paginated, searchable).
     */
    public function posts(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $posts = $this->postService->getPaginatedPosts($perPage, $search);

        return PostResource::collection($posts);
    }

    /**
     * Get all comments for admin management (paginated, searchable).
     */
    public function comments(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        $search = $request->query('search');
        $perPage = $request->query('per_page', 15);
        $comments = $this->commentService->getPaginatedComments($perPage, $search);

        return CommentResource::collection($comments);
    }
}
