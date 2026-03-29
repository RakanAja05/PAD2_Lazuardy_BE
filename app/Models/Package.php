<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    /** @use HasFactory<\Database\Factories\PackageFactory> */
    use HasFactory;

    protected $fillable =
    [
        'name',
        'session',
        'price',
        'discount',
        'description',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'description' => 'array',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'package_id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'orders_items', 'package_id', 'order_id')
            ->withPivot(['qty', 'price', 'subtotal'])
            ->withTimestamps();
    }
}
