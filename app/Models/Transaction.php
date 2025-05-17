<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'trx_id',
        'transaction_group_id',
        'address',
        'total_amount',
        'duration',
        'product_id',
        'quantity',
        'store_id',
        'started_at',
        'ended_at',
        'delivery_type',
        'proof',
        'is_paid',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'total_amount' => MoneyCast::class,
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_paid' => 'boolean',
    ];

    public static function generateUniqueTrxId()
    {
        $prefix = 'ABE';
        $randomNumber = rand(1000, 9999);
        $trxId = $prefix . $randomNumber;

        while (self::where('trx_id', $trxId)->exists()) {
            $randomNumber = rand(1000, 9999);
            $trxId = $prefix . $randomNumber;
        }

        return $trxId;
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to find transactions in the same group
     */
    public function scopeInSameGroup($query, $transactionGroupId)
    {
        return $query->where('transaction_group_id', $transactionGroupId);
    }

    /**
     * Get all transactions in the same group.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGroupTransactionsAttribute()
    {
        if ($this->transaction_group_id) {
            return self::where('transaction_group_id', $this->transaction_group_id)
                ->where('id', '!=', $this->id)
                ->with('product')
                ->get();
        }

        return collect();
    }
}