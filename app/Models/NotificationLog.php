<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'fingerprint',
        'event_type',
        'ticket_id',
        'recipient_emails',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'recipient_emails' => 'array',
            'sent_at'          => 'datetime',
        ];
    }
}