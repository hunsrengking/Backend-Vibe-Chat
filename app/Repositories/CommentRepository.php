<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentRepository implements CommentRepositoryInterface
{
    public function getByPostId(int $postId): Collection
    {
        return Comment::where('post_id', $postId)
            ->whereNull('parent_id')
            ->with(['guest', 'replies'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function create(array $data): Comment
    {
        $comment = Comment::create($data);
        return $comment->load(['guest', 'replies']);
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }

    public function findById(int $id): ?Comment
    {
        return Comment::find($id);
    }

    public function getStats(): array
    {
        return [
            'total_comments' => Comment::count(),
        ];
    }

    public function getPaginated(int $perPage, ?string $search = null): LengthAwarePaginator
    {
        $query = Comment::with(['guest', 'post'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($gQ) use ($search) {
                      $gQ->where('nickname', 'like', "%{$search}%");
                  });
            });
        }

        return $query->paginate($perPage);
    }
}
