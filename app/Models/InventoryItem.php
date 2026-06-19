<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'sku',
        'quantity',
        'min_quantity',
        'unit_price',
        'unit',
        'supplier',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'min_quantity' => 'integer',
    ];

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_quantity;
    }
}
