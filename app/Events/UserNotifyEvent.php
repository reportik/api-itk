<?php

namespace App\Events;

//use Muliix\User;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UserNotifyEvent extends Event implements ShouldBroadcastNow
{
    use SerializesModels;

    public $user;
    public $details;
    /** 
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $details)
    {
        $this->user = $user;
        $this->details = $details;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['user_' . $this->user];
    }

    public function broadcastAs()
    {
        return 'notify-event';
    }
}


