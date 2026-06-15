<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Support\Collection;

interface CommentRepositoryInterface
{
    public function getByPostId(int $postId): Collection;
    public function create(array $data): Comment;
    public function delete(Comment $comment): bool;
    public function findById(int $id): ?Comment;
    public function getStats(): array;
    public function getPaginated(int $perPage, ?string $search = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
