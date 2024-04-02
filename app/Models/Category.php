<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $primaryKey = 'id';
    
  
    protected $fillable = [
        'id',
        'name',
        'categoryImage',
    ];

    public function subcateDetails()
    {
        return $this->hasMany(SubCategory::class,'category_id','id');
    }
    public function productDetails()
    {
        return $this->hasMany(ProductList::class,'subcategory_id','id');
    }
    public function catsubcateDetails()
    {
        return $this->hasMany(SubCategory::class,'category_id','id');
    }
    public function subcateDetailss()
    {
        return $this->hasMany(SubCategory::class,'category_id','id');
    }
    public function subcategory()
    {
        return $this->hasMany(SubCategory::class,'category_id','id');
    }
}
?>