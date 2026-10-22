<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_phone',
        'message',
        'status',
        'gateway_response',
        'gateway_message_id',
        'notifiable_type',
        'notifiable_id',
        'notification_id',
        'cost',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    /**
     * Get the owning notifiable model.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
