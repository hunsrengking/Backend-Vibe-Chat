<?php

namespace App\Repositories;

use App\Models\Guest;

interface GuestRepositoryInterface
{
    public function create(array $data): Guest;
    public function findByToken(string $token): ?Guest;
    public function findByUsername(string $username): ?Guest;
    public function updateNickname(Guest $guest, string $nickname): Guest;
    public function toggleAdmin(Guest $guest, bool $isAdmin): Guest;
    public function getPaginated(int $perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    public function delete(Guest $guest): bool;
    public function getStats(): array;
}
