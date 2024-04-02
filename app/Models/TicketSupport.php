<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSupport extends Model
{
    use HasFactory;
    protected $table = 'ticketsupport';
    protected $fillable = [
        'ticketNumber',
        'customer_id',
        'suplier_id',
        'order_id',
        'complainBy',
        'comment',
        'commentType',
        'commentCount',
    ];
}
