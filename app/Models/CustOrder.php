<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustOrder extends Model
{
    use HasFactory;
    protected $table = 'cust_orders';
    protected $fillable = [
        'id',
        'request_id',
        'response_id',
        'supplier_id',
        'customer_id',
        'product_id',
        'quantity',
        'status',
        'unit_price',
    ];

     public function reqResponse(){
        return $this->hasMany(RequestResponse::class,'id','response_id');
       } 
      
       public function productname(){
        return $this->hasMany(ProductList::class,'id','product_id');
       }
}
