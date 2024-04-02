<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminCreditPoints extends Model
{
    use HasFactory;
    protected $table = 'admin_credit_points';
    protected $fillable = [
        'id',
        'credit_point',
        'amount',
        'supplier_id',
    ];
}
