<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catsubcatprodmapping extends Model
{
    use HasFactory;
    protected $table = 'catsubcatprodmapping';
    protected $primaryKey = 'id';
    
  
    protected $fillable = [
        'id',
        'category_id',
        'subcategory_id',
        'product_id',
        'productDesc',
        'unitofMeasurement',
        'productImage',
    ];
}
