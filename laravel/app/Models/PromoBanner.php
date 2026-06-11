<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PromoBanner extends Model
{
    use HasUuids;
    protected $fillable = [
        'key', 'title', 'subtitle', 'cta_label', 'cta_url', 'image_url', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
