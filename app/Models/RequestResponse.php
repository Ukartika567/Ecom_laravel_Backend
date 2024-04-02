<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestResponse extends Model
{
    use HasFactory;
    protected $table = 'request_response';
    protected $fillable = [
        'id',
        'resp_ref_id',
        'request_quote_id',
        'suplierid',
        'customerid',
        'category_id',
        'subcategory_id',
        'product_id',
        'description',
        'productImage',
        'unit_of_measurement',
        'whole_price_per_unit',
        'min_order_qty',
        'special_offer_deals',
        'packaging_detail',
        'qty_per_packet',
        'ship_methods',
        'estimated_delivery_time',
        'requiredtime',
        'tax',
        'discount',
        'shipping',
    ];

    public function userdata(){
        return $this->hasMany(User::class,'id','id');
    }

    public function productname(){
        return $this->hasMany(ProductList::class,'id','product_id');
       }

       public function negotiations(){
        return $this->hasMany('App\Models\Negotiation','response_id','id');
       }
   
       public function productsname(){
        return $this->belongsTo(Product::class,'id','product_id');
       }
      
}
