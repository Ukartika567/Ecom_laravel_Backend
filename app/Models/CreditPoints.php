<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPoints extends Model
{
    use HasFactory;
    protected $table = 'credit_points';
    protected $fillable = [
        'id',
        'user_id',
        'credit_point',
    ];
}
