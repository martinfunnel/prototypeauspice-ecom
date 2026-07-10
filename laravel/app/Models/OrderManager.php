<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderManager extends Model
{
    protected $fillable = ['name', 'phone', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
