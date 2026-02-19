<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_id',
        'notifiable_id',
        'type',
        'title',
        'message',
        'image',
        'audience',
        'is_read',
        'scheduled_at',
        'is_sent',
        'status',
    ];

    protected $casts = [
        'audience' => 'array',
        'scheduled_at' => 'datetime',
        'is_read' => 'boolean',
        'is_sent' => 'boolean',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    // Scope for active notifications
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    // Scope for scheduled notifications
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')->where('is_sent', false);
    }
}