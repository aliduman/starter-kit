<?php

namespace App\Modules\Ecommerce\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_orders';

    protected $fillable = [
        'user_id',
        'reference',
        'gateway',
        'status',
        'total',
        'currency',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
