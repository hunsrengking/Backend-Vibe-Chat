<?php

namespace App\Repositories;

use App\Models\ChatMessage;
use Illuminate\Support\Collection;

interface ChatRepositoryInterface
{
    public function getRecent(int $limit = 50, ?int $groupChatId = null): Collection;
    public function create(array $data): ChatMessage;
}
