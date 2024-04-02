<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    use HasFactory;
    protected $table = 'negotiations';
    protected $fillable = [
        'id',
        'request_id',
        'response_id',
        'supplier_id',
        'customer_id',
        'product_id',
        'quantity',
        'unit_price',
        'status',
    ];
    public function requestQuote(){
        return $this->hasOne(RequestQuote::class, 'id','request_id');
    }
}
