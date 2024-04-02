<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelledQuotes extends Model
{
    use HasFactory;
    protected $table = 'cancelledquotes';
    protected $primaryKey = 'id';

    protected $fillable =[
        'id',
        'quote_id',
        'user_id',
        'status'
    ];
    public function requestedquote(){
        return $this->hasMany(RequestedQuotes::class, 'id', 'quote_id');
    }
}
