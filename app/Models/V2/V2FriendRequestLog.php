<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class V2FriendRequestLog extends Model
{
    protected $table = 'friend_requests';

    protected $fillable = [
        'from_parent_id',
        'to_parent_id',
        'status',
        'responded_at',
        'message',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Get the sender user.
     */
    public function sender()
    {
        return $this->belongsTo(\App\Models\User::class, 'from_parent_id');
    }

    /**
     * Get the receiver user.
     */
    public function receiver()
    {
        return $this->belongsTo(\App\Models\User::class, 'to_parent_id');
    }

    /**
     * Scope for sent requests.
     */
    public function scopeSentBy($query, $userId)
    {
        return $query->where('from_parent_id', $userId);
    }

    /**
     * Scope for received requests.
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('to_parent_id', $userId);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for recent requests.
     */
    public function scopeRecent($query, $limit = 5)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
