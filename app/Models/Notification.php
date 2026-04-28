<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'type',
        'title',
        'message',
        'is_read',
        'related_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
