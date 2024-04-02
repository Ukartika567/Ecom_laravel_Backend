<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetails extends Model
{
    use HasFactory;
    protected $table = 'invoicedetails';
    protected $fillable = [
        'id',
        'order_id',
        'user_id',
        'company_name',
        'address',
        'city',
        'country',
        'zipcode',
        'invoice_no',
        'delivery_date',
        'invoice_due_date',
        'shipvia',
        'shipmethod',
        'shipterms'
    ];
}
