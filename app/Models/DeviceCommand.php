<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    protected $fillable = [
        'command',
        'payload',
        'status',
        'result',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
    ];
}
