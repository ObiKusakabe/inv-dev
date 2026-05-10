<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_date',
        'method',
        'amount',
        'received_by',
        'note',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
