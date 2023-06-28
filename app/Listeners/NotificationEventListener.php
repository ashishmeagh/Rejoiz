<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\NotificationsModel;

class NotificationEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(NotificationsModel $notifications_model)
    {   
        $this->NotificationsModel = $notifications_model;
    }

    /**
     * Handle the event.
     *
     * @param  NotificationEvent  $event
     * @return void
     */
    public function handle(NotificationEvent $event)
    {
        $arr_notification_data['from_user_id']  = $event->from_user_id;
        $arr_notification_data['to_user_id']    = $event->to_user_id;
        $arr_notification_data['title']         = $event->title;
        $arr_notification_data['description']   = $event->description;
        $arr_notification_data['type']          = $event->type;
        $arr_notification_data['notification_url']  = $event->link;

        $this->NotificationsModel->create($arr_notification_data);

    }
}
