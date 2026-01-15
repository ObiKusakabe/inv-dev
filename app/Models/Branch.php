<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['code', 'name'];
    
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
