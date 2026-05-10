<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'supplier_id',
        'size',
        'stock',
        'min_stock',
        'is_consignment',
        'supplier_price',
        'sell_price',
        'image',
    ];

    protected $casts = [
        'is_consignment' => 'boolean',
        'supplier_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(SupplierData::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function stockForBranch(int $branchId)
    {
        return $this->hasOne(ProductStock::class)->where('branch_id', $branchId);
    }
}