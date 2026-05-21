<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectFile extends Model
{
    protected $table = 'objects';

    const UPDATED_AT = null;

    protected $fillable = [
        'resource_id',
        'key',
        'size_mb',
        'mime_type',
        'storage_path',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }
}