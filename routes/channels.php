<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat', function ($user) {
    if ($user) {
        return [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'avatar_url' => $user->avatar_url,
            'is_admin' => (bool) $user->is_admin,
        ];
    }
    return null;
});

Broadcast::channel('group-chat.{groupId}', function ($user, $groupId) {
    $isMember = \DB::table('group_chat_members')
        ->where('group_chat_id', (int) $groupId)
        ->where('guest_id', $user->id)
        ->exists();

    if ($user && $isMember) {
        return [
            'id' => $user->id,
            'nickname' => $user->nickname,
            'avatar_url' => $user->avatar_url,
            'is_admin' => (bool) $user->is_admin,
        ];
    }
    return null;
});

Broadcast::channel('guest-notifications.{guestId}', function ($user, $guestId) {
    return (int) $user->id === (int) $guestId;
});

Broadcast::channel('direct-chat.{guestId}', function ($user, $guestId) {
    return (int) $user->id === (int) $guestId;
});

Broadcast::channel('call-signal.{id1}.{id2}', function ($user, $id1, $id2) {
    return (int) $user->id === (int) $id1 || (int) $user->id === (int) $id2;
});
