<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;
    protected $table = 'userprofiles';
    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'user_id',
        'profile_picture',
        'gender',
        'date_of_birth',
        'mobile',
        'category',
        'subcategory',
        'business_sname',
        'industry',
        'address',
        'zipcode',
        'city',
        'state',
        'country',
        'credit_points',
        'profile_rating',
        'approval_status'
    ];
    public function getuserdetails(){
        return $this->hasOne('App\Models\User', 'id');
    }
    public function userbankdetails(){
        return $this->hasOne('App\Models\UserBankDetails', 'user_id', 'user_id');
       }
       public function usersdata(){
        return $this->hasMany('App\Models\User','id', 'user_id');
       }
      
        public function customerlist(){
        return $this->hasOne('App\Models\User','id','user_id');
       }
       public function supplierlist(){
        return $this->hasMany('App\Models\User','id', 'user_id');
       }
       public function  requestQuoteData(){
        return $this->hasMany('App\Models\RequestQuote','product', 'product_id');
       }
}

?>
