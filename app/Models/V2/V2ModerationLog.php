<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class V2ModerationLog extends Model
{
    protected $table = 'v2_moderation_logs';

    protected $fillable = [
        'user_id',
        'content_type',
        'content_id',
        'content_text',
        'violation_type',
        'matched_keywords',
        'ai_response',
        'action',
        'reason',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'matched_keywords' => 'array',
        'ai_response' => 'array',
    ];

    /**
     * Get the user whose content was moderated.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Scope for blocked content.
     */
    public function scopeBlocked($query)
    {
        return $query->where('action', 'blocked');
    }

    /**
     * Scope for flagged content.
     */
    public function scopeFlagged($query)
    {
        return $query->where('action', 'flagged');
    }

    /**
     * Scope by violation type.
     */
    public function scopeByViolationType($query, $type)
    {
        return $query->where('violation_type', $type);
    }
}
