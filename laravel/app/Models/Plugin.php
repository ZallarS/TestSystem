<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name', 'provider', 'active', 'settings',
        'version', 'description', 'author', 'requires'
    ];

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'array',
        'requires' => 'array',
    ];
}
