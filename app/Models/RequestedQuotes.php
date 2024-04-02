<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestedQuotes extends Model
{
    use HasFactory;
    protected $table = 'requestedquotes';
    protected $fillable = [
        'product',
        'qty',
        'customerid',
        'requiredtime',
        'category',
        'subcategory',
        'companyname',
    ];
    public function quoteproductname(){
        return $this->hasMany(ProductList::class,'id','product');
       } 
}
