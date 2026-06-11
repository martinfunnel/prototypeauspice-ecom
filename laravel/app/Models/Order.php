<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;
    protected $fillable = [
        'order_number', 'customer_name', 'customer_phone',
        'commune_id', 'commune_name', 'address', 'notes',
        'subtotal', 'delivery_fee', 'total', 'status'
    ];

    protected $casts = [
        'subtotal' => 'decimal:0',
        'delivery_fee' => 'decimal:0',
        'total' => 'decimal:0',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeConfirmed($query) { return $query->where('status', 'confirmed'); }
    public function scopeShipped($query) { return $query->where('status', 'shipped'); }
    public function scopeDelivered($query) { return $query->where('status', 'delivered'); }
    public function scopeCancelled($query) { return $query->where('status', 'cancelled'); }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'processing' => 'En préparation',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            default => 'Inconnu',
        };
    }
}
