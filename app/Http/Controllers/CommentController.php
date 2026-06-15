<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Get comments for a specific post.
     */
    public function index($postId)
    {
        $comments = $this->commentService->getCommentsByPost($postId);
        return CommentResource::collection($comments);
    }

    /**
     * Store a comment (or nested comment).
     */
    public function store(CreateCommentRequest $request)
    {
        $guest = $request->user();
        $comment = $this->commentService->createComment($request->validated(), $guest);
        
        return new CommentResource($comment);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Request $request, Comment $comment)
    {
        $guest = $request->user();

        // Authorization: only owners or admins can delete
        if ($comment->guest_id !== $guest->id && !$guest->is_admin) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $this->commentService->deleteComment($comment);

        return response()->json(['message' => 'Comment deleted successfully.']);
    }
}
