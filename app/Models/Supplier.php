<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name','contact'];

    public function product()
    {
        return $this->hasMany(Product::class);
    }
    public function user()
    {
        return $this->hasOne(User::class);
    }
}
