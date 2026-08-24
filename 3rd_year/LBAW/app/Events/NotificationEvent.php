<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Notification $notification) {}

    public function broadcastOn()
    {
        \Log::info('Broadcasting notification', [
            'user' => $this->notification->receiver_id
        ]);

        return new PrivateChannel('user.' . $this->notification->receiver_id);
    }

    public function broadcastAs()
    {
        return 'notification-created';
    }

    public function broadcastWith()
    {
        return [
            'notification_card' => view(
                'partials.notification_card',
                ['notification' => $this->notification]
            )->render()
        ];
    }
}
