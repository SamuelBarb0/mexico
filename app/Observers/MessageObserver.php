<?php

namespace App\Observers;

use App\Models\Message;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message): void
    {
        // Solo contar mensajes outbound (enviados)
        if ($message->direction === 'outbound' && $message->tenant && $message->tenant->limits) {
            $message->tenant->limits->increment('current_messages_this_month');
        }
    }
}
