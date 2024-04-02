<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'id';
    
  
    protected $fillable = [
        'id',
        'product_id',
        'user_id',
        'name',
        'description',
        'productImage',
        'unit_of_measurement',
        'whole_price_per_unit',
        'max_order_qty',
        'min_order_qty',
        'special_offer_deals',
        'packaging_detail',
        'ship_methods',
        'estimated_days',

    ];
    public function productDetails()
    {
        return $this->hasMany(Product::class,'subcategory_id','id');
    }
    public function product()
    {
        return $this->hasMany(ProductList::class,'id','product_id');
    }

    public function subcategoryname(){
        return $this->hasMany(SubCategory::class,'id','subcategory_id');
       }
       public function quotesubcategoryname(){
        return $this->hasMany(SubCategory::class,'id','subcategory_id');
       }
       public function productList()
       {
           return $this->belongsTo(ProductList::class);
       }
}
?>