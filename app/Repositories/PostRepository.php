<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\Guest;
use App\Models\Like;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function getPaginated(int $perPage, ?string $search = null): LengthAwarePaginator
    {
        $query = Post::with(['guest', 'likes'])
            ->withCount('comments')
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

    public function getTrending(int $limit = 5): Collection
    {
        // Trending is based on likes_count + comments_count within the last few days,
        // or simply order by likes_count and comments_count descending.
        return Post::with(['guest', 'likes'])
            ->withCount('comments')
            ->orderByRaw('likes_count + (select count(*) from comments where comments.post_id = posts.id) DESC')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function create(array $data): Post
    {
        $post = Post::create($data);
        return $post->load(['guest', 'likes'])->loadCount('comments');
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        return $post->load(['guest', 'likes'])->loadCount('comments');
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    public function findById(int $id): ?Post
    {
        return Post::with(['guest', 'likes'])->withCount('comments')->find($id);
    }

    public function toggleLike(Post $post, Guest $guest): bool
    {
        $like = Like::where('post_id', $post->id)
            ->where('guest_id', $guest->id)
            ->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            return false; // unliked
        } else {
            Like::create([
                'post_id' => $post->id,
                'guest_id' => $guest->id,
            ]);
            $post->increment('likes_count');
            return true; // liked
        }
    }

    public function getStats(): array
    {
        return [
            'total_posts' => Post::count(),
            'total_likes' => Like::count(),
        ];
    }
}
