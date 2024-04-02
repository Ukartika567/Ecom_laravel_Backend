<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class UserRole extends Model
{
    use HasFactory;
    protected $table = 'roles';
    protected $primaryKey = 'id';
    
  
    protected $fillable = [
        'id',
        'type',
    ];
    public function getuser(){
        return $this->hasOne('App\Models\User', 'role_id');
       }
}
?>