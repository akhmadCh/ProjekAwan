<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    const CREATED_AT = 'logged_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}