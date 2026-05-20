<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $fillable = ['name', 'storage_quota_gb', 'price_per_month', 'description'];

    public function userSubscriptions() {
        return $this->hasMany(UserSubscription::class, 'package_id');
    }
}