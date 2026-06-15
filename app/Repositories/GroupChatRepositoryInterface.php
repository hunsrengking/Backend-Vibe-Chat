<?php

namespace App\Repositories;

use App\Models\GroupChat;
use Illuminate\Support\Collection;

interface GroupChatRepositoryInterface
{
    public function getAll(?int $guestId = null): Collection;
    public function create(array $data): GroupChat;
    public function join(int $groupChatId, int $guestId, ?string $passcode = null): array;
    public function getPendingRequests(int $groupChatId): Collection;
    public function updateRequestStatus(int $groupChatId, int $requestId, string $status): bool;
    public function isMember(int $groupChatId, int $guestId): bool;
}
