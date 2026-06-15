<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Guest;
use App\Repositories\CommentRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    protected CommentRepositoryInterface $commentRepository;
    protected SpamFilterService $spamFilter;

    public function __construct(CommentRepositoryInterface $commentRepository, SpamFilterService $spamFilter)
    {
        $this->commentRepository = $commentRepository;
        $this->spamFilter = $spamFilter;
    }

    public function getCommentsByPost(int $postId): Collection
    {
        return $this->commentRepository->getByPostId($postId);
    }

    public function createComment(array $data, Guest $guest): Comment
    {
        // Profanity Check
        $this->spamFilter->checkText($data['content'], 'content');

        $data['guest_id'] = $guest->id;

        $comment = $this->commentRepository->create($data);

        // Fetch post to check post author
        $post = \App\Models\Post::find($comment->post_id);
        
        // Dispatch real-time notification to the post author if someone else commented!
        if ($post && $post->guest_id !== $guest->id) {
            broadcast(new \App\Events\CommentNotification($post->guest_id, $comment))->toOthers();
        }

        return $comment;
    }

    public function deleteComment(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment);
    }

    public function getCommentById(int $id): ?Comment
    {
        return $this->commentRepository->findById($id);
    }

    public function getPaginatedComments(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return $this->commentRepository->getPaginated($perPage, $search);
    }
}
