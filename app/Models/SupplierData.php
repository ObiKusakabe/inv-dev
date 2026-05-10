<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierData extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'contact',
        'note',
    ];

    /**
     * Relasi ke products (kalau nanti mau dipakai)
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}