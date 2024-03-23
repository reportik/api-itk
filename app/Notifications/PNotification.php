<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\FCM\FCMChannel;
use Kreait\Firebase\Messaging\CloudMessage;

class PNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return [FCMChannel::class];
    }

    public function toFCM($notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withDefaultSounds()
            ->withNotification([
                'title' => 'Order shipped',
                'body' => 'Your order for laptop is shipped.',
            ])
            ->withData([
                'orderId' => '#123'
            ]);
    }
}
