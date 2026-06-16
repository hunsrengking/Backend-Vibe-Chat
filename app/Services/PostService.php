<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Guest;
use App\Repositories\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PostService
{
    protected PostRepositoryInterface $postRepository;
    protected SpamFilterService $spamFilter;

    public function __construct(PostRepositoryInterface $postRepository, SpamFilterService $spamFilter)
    {
        $this->postRepository = $postRepository;
        $this->spamFilter = $spamFilter;
    }

    public function getPaginatedPosts(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return $this->postRepository->getPaginated($perPage, $search);
    }

    public function getTrendingPosts(int $limit = 5): Collection
    {
        return $this->postRepository->getTrending($limit);
    }

    public function createPost(array $data, Guest $guest, $mediaFile = null): Post
    {
        // Profanity Check (only if content is present)
        if (isset($data['content']) && !empty($data['content'])) {
            $this->spamFilter->checkText($data['content'], 'content');
        }

        $data['guest_id'] = $guest->id;
        $data['likes_count'] = 0;

        // Process media file if uploaded
        if ($mediaFile) {
            $path = $mediaFile->store('posts', env('FILESYSTEM_DISK', 'public'));
            $data['media_path'] = $path;
            
            $mime = $mediaFile->getClientMimeType();
            $data['media_type'] = str_contains($mime, 'video') ? 'video' : 'image';
        }

        return $this->postRepository->create($data);
    }

    public function updatePost(Post $post, array $data): Post
    {
        // Profanity Check
        if (isset($data['content']) && !empty($data['content'])) {
            $this->spamFilter->checkText($data['content'], 'content');
        }

        return $this->postRepository->update($post, $data);
    }

    public function deletePost(Post $post): bool
    {
        return $this->postRepository->delete($post);
    }

    public function toggleLikePost(Post $post, Guest $guest): bool
    {
        return $this->postRepository->toggleLike($post, $guest);
    }

    public function getPostById(int $id): ?Post
    {
        return $this->postRepository->findById($id);
    }
}
