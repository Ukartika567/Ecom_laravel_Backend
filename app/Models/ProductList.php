<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductList extends Model
{
    use HasFactory;
    protected $table = 'productlist';
    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'subcategory_id',
        'name',
        'productImage',
    ];

    public function productDetails()
    {
        return $this->hasMany(ProductList::class,'subcategory_id','id');
    }

    public function subcategoryname(){
        return $this->hasMany(SubCategory::class,'id','subcategory_id');
       }
       public function quotesubcategoryname(){
        return $this->hasMany(SubCategory::class,'id','subcategory_id');
       }
    public function productInformation(){
        return $this->hasMany(Product::class,'product_id', 'id');
    }
} 
?>