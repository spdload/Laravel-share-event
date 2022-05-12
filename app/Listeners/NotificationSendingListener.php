<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSending;

class NotificationSendingListener
{
    public function handle(NotificationSending $event)
    {
        if (method_exists($event->notification, 'dontSend')) {
            return ! $event->notification->dontSend($event->notifiable);
        }

        return true;
    }
}
