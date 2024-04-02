<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CutomerSupport extends Model
{
    use HasFactory;
    protected $table = 'customer_support';
    protected $primaryKey = 'id';

    protected $fillable=[
        'id',
        'user_id',
        'phoneno',
        'supporthr',
        'email',
        'supportaddr'
    ];
}
