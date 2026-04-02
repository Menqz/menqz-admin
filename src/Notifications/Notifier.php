<?php

namespace MenqzAdmin\Admin\Notifications;

use MenqzAdmin\Admin\Notifications\Database\Notification;

class Notifier
{
    public static function notifyUser(int $userId, string $title, ?string $description = null): Notification
    {
        return static::notify([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
        ]);
    }

    public static function notifyRole(int $roleId, string $title, ?string $description = null): Notification
    {
        return static::notify([
            'role_id' => $roleId,
            'title' => $title,
            'description' => $description,
        ]);
    }

    public static function notify(array $attributes): Notification
    {
        $notification = Notification::create($attributes);

        if (
            config('admin.notifications.enabled') &&
            config('admin.notifications.pusher.enabled') &&
            config('broadcasting.default') === 'pusher' &&
            class_exists(\Pusher\Pusher::class)
        ) {
            event(new Events\NotificationCreated($notification));
        }

        return $notification;
    }
}

