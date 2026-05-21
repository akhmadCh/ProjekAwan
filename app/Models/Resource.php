<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    use SoftDeletes; // Mengaktifkan fitur deleted_at

    protected $fillable = [
        'user_id',
        'subscription_id',
        'name',
        'type',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class);
    }

    // Sumber daya ini menampung banyak file/object
    public function objects(): HasMany
    {
        return $this->hasMany(ObjectFile::class, 'resource_id');
    }
}