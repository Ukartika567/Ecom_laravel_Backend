<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditMapping extends Model
{
    use HasFactory;
    protected $table = 'creditpoints_history';
    protected $fillable = [
        'id',
        'credit_point',
        'supplier_id',
        'credit_use',
    ];
}
