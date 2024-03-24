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

    public $details = [];

    public function __construct( $details)
    {
        $this->details = $details;
    }
    public function via($notifiable): array
    {
        return [FCMChannel::class];
    }

    public function toFCM($notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withDefaultSounds()
            ->withNotification([
                'title' => $this->details['title'],
                'body' => $this->details['body'],
            ])
            ->withData($this->details);
    }
}
