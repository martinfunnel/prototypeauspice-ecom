<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;
    protected $fillable = [
        'name', 'slug', 'description', 'short_description',
        'price', 'promo_price', 'images', 'detail_images', 'benefits',
        'stock', 'category_id', 'is_active', 'is_popular'
    ];

    protected $casts = [
        'price' => 'decimal:0',
        'promo_price' => 'decimal:0',
        'images' => 'array',
        'detail_images' => 'array',
        'benefits' => 'array',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function displayPrice(): float
    {
        return (float) ($this->promo_price ?? $this->price);
    }

    public function hasPromo(): bool
    {
        return $this->promo_price !== null && $this->promo_price < $this->price;
    }
}
