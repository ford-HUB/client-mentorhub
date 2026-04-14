<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat-{roomId}', function ($user, $roomId) {
    if (!$user || !$user->id) {
        return null;
    }

    return ['id' => $user->id];
}, ['guards' => ['student', 'tutor']]);

Broadcast::channel('user-{userType}-{userId}', function ($user, $userType, $userId) {
    if (!$user || !$user->id) {
        return null;
    }

    return (int) $user->id === (int) $userId
        ? ['id' => $user->id]
        : null;
}, ['guards' => ['student', 'tutor']]);

Broadcast::channel('call-{roomId}', function ($user, $roomId) {
    if (!$user || !$user->id) {
        return null;
    }

    return ['id' => $user->id];
}, ['guards' => ['student', 'tutor']]);