<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subcategory extends Model
{
    use HasFactory;
    protected $table = 'subcategories';
    protected $primaryKey = 'id';
    
  
    protected $fillable = [
        'id',
        'category_id',
        'name',
        'subcategoryImage'
    ];
    public function product()
    {
        return $this->hasMany(ProductList::class,'subcategory_id', 'id');
    }

    public function productDetails()
    {
        return $this->hasMany(ProductList::class,'subcategory_id', 'id');
    }
    public function categoryname(){
        return $this->hasMany(Category::class,'id','category_id');
       }
}
?>