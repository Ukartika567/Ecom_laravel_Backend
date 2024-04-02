<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
  
    protected $table = 'users';
    
    protected $fillable = [
           'role_id',
        'name',
        'username',
        'email',
        'password',
        'password_txt',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims()  {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

   public function userprofile(){
    return $this->hasOne('App\Models\UserProfile','user_id');
   }

   public function userbankdetails(){
    return $this->hasOne('App\Models\UserBankDetails', 'id');
   }

   public function usershippingaddress(){
    return $this->hasOne('App\Models\ShippingAddress', 'id');
   }
   public function userrole(){
    return $this->hasOne('App\Models\UserRole', 'id');
   }
   public function categoryname(){
    return $this->hasMany(Category::class,'category','id');
   }
   public function usersdata(){
    return $this->hasMany('App\Models\User','customerid', 'id');
   }

   public function orderrating(){
    return $this->hasMany(Feedback::class, 'user_id', 'id');
   }
}
