<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBankDetails extends Model
{
    use HasFactory;
    protected $table = 'userbankdetails';
    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'user_id',
        'bank_name',
        'branch_name',
        'micr_code',
        'ifsc_code',
        'account_type',
        'account_number',
        'account_balance',
        'fd_link',
    ];
    public function getuserdata(){
        return $this->hasOne('App\Models\User', 'id');
    }
    public function usershippingaddress(){
        return $this->hasOne('App\Models\ShippingAddress', 'id');
       }
}

?>