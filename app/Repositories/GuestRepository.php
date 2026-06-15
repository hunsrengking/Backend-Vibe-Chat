<?php

namespace App\Repositories;

use App\Models\Guest;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GuestRepository implements GuestRepositoryInterface
{
    public function create(array $data): Guest
    {
        return Guest::create($data);
    }

    public function findByToken(string $token): ?Guest
    {
        return Guest::where('guest_token', $token)->first();
    }

    public function findByUsername(string $username): ?Guest
    {
        return Guest::where('username', $username)->first();
    }

    public function updateNickname(Guest $guest, string $nickname): Guest
    {
        $guest->update(['nickname' => $nickname]);
        return $guest;
    }

    public function toggleAdmin(Guest $guest, bool $isAdmin): Guest
    {
        $guest->update(['is_admin' => $isAdmin]);
        return $guest;
    }

    public function getPaginated(int $perPage): LengthAwarePaginator
    {
        return Guest::orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function delete(Guest $guest): bool
    {
        return $guest->delete();
    }

    public function getStats(): array
    {
        return [
            'total_guests' => Guest::count(),
            'total_admins' => Guest::where('is_admin', true)->count(),
        ];
    }
}
