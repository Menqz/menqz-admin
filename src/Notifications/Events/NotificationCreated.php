<?php

namespace MenqzAdmin\Admin\Notifications\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MenqzAdmin\Admin\Notifications\Database\Notification;

class NotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->notification->user_id) {
            $channels[] = new PrivateChannel('menqz-admin.notifications.user.'.$this->notification->user_id);
        }

        if ($this->notification->role_id) {
            $channels[] = new PrivateChannel('menqz-admin.notifications.role.'.$this->notification->role_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'menqz-admin.notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'description' => $this->notification->description,
            'icon' => $this->notification->icon ?: 'icon-bell',
            'redirect_url' => $this->notification->url_redirect ?: admin_url('notifications/'.$this->notification->id.'/edit'),
            'redirect_title' => $this->notification->title_redirect ?: 'Visualizar',
            'created_at' => optional($this->notification->created_at)->toISOString(),
        ];
    }
}
