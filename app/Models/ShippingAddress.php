<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shippingaddress extends Model
{
    use HasFactory;
    protected $table = 'shippingaddress';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'user_id',
        'address',
        'zipcode',
        'city',
        'state',
        'country',
    ];

    public function getuserdata()
    {
        return $this->hasOne('App\Models\User', 'id');
    }
}
