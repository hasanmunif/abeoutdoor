<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'thumbnail',
        'about',
        'category_id',
        'brand_id',
        'price',
        'stock',
        'can_multi_quantity',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    protected $casts = [
        'price' => MoneyCast::class,
        'can_multi_quantity' => 'boolean',
    ];

    /**
     * Check if product is available (stock > 0)
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->stock > 0;
    }

    /**
     * Calculate rental price based on duration (in days)
     * Price is for every 3 days
     *
     * @param int $days
     * @return float
     */
    public function calculateRentalPrice($days)
    {
        // Calculate how many 3-day periods
        $periods = ceil($days / 3);
        return $this->price * $periods;
    }

    /**
     * Decrease stock when product is rented
     *
     * @param int $quantity
     * @return void
     */
    public function decreaseStock($quantity = 1)
    {
        if ($this->stock >= $quantity) {
            $this->stock -= $quantity;
            $this->save();
        }
    }

    /**
     * Increase stock when product is returned
     *
     * @param int $quantity
     * @return void
     */
    public function increaseStock($quantity = 1)
    {
        $this->stock += $quantity;
        $this->save();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class);
    }
}