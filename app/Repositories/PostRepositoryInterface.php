<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\Guest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PostRepositoryInterface
{
    public function getPaginated(int $perPage, ?string $search = null): LengthAwarePaginator;
    public function getTrending(int $limit = 5): Collection;
    public function create(array $data): Post;
    public function update(Post $post, array $data): Post;
    public function delete(Post $post): bool;
    public function findById(int $id): ?Post;
    public function toggleLike(Post $post, Guest $guest): bool;
    public function getStats(): array;
}
