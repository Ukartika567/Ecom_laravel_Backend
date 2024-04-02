<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProductsMapping extends Model
{
    use HasFactory;
    protected $table = 'suplierproductsmapping';
    protected $fillable = [
        'id',
        'supplier_id',
        'category',
        'subcategory',
        'product_id',
    ];
 
    
    public function productDetails()
    {
        return $this->hasMany(ProductList::class,'id','product_id');
    }
    public function productInfo(){
        return $this->hasMany(Product::class,'user_id', 'supplier_id');
    }  
    public function productinformation(){
        return $this->hasMany(Product::class,'product_id','product_id');
    } 

    public function usersdata(){
        return $this->hasMany(User::class,'id', 'supplier_id');
    }
}
