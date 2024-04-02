<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestEmail extends Model
{
    use HasFactory;
    protected $table = 'reqemail';
    protected $fillable = [
        'id',
        'req_id',
        'supplier_id',
        'email_status',  
    ];


    public function userdata(){
        return $this->hasMany('App\Models\UserProfile','user_id','supplier_id');
       }
}
