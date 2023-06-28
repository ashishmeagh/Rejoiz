<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Array $arr_notification_data)
    {

        $this->from_user_id = 0;
        $this->to_user_id   = 0;
        $this->description  = "";
        $this->title        = "";
        $this->type         = "";
        $this->link         = '';

        if(isset($arr_notification_data['from_user_id']))
        {
            $this->from_user_id  = $arr_notification_data['from_user_id'];
        }

        if(isset($arr_notification_data['to_user_id']))
        {
            $this->to_user_id   = $arr_notification_data['to_user_id'];
        }

        if(isset($arr_notification_data['description']))
        {
            $this->description   = $arr_notification_data['description'];
        }

        if(isset($arr_notification_data['title']))
        {
            $this->title   = $arr_notification_data['title'];
        }

        if(isset($arr_notification_data['type']))
        {
            $this->type   = $arr_notification_data['type'];
        } 
        if(isset($arr_notification_data['link']))
        {
           $this->link = $arr_notification_data['link'];


        }   
        
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {   
        return [];
        // return new PrivateChannel('channel-name');
    }
}
