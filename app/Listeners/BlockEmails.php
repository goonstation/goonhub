<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;

class BlockEmails
{
    public function handle(MessageSending $event)
    {
        $to = $event->message->getTo();
        $to = array_filter($to, function ($address) {
            return ! str_ends_with($address->getAddress(), '@null.local');
        });

        if (count($to) > 0) {
            $event->message->to(...$to);
        } else {
            return false;
        }
    }
}
