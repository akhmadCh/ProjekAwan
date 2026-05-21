<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPackage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'quota_limit_gb',
        'price_per_month',
        'description',
    ];

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'package_id');
    }
}