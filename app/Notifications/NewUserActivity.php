<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewUserActivity extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Welcome to the application')
            ->body('This is notification body content. You are successfully subscribed!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome to the application',
            'body' => 'This is notification body content. You are successfully subscribed!',
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
