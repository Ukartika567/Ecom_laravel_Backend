<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'suplier';
    protected $fillable = [
        'suplierUniqueId',
        'companyName',
        'contactPersonName',
        'businessAddress',
        'contactEmail',
        'contactPhoneNumber',
        'wholesalePricePerUnit',
        'minimumOrderQuantity',
        'specialOffers',
        'packagingDetails',
        'estimatedDeliveryTimes',
        'yearsInBusiness',
        'numberOfCustomersServed',
        'leadTimeForOrderFulfillment',
        'contactInformationforCustomerQueries',
        'customerSupportHours',
        'platformUsageAgreement',
        'supplierGuidelines',
    ]; 
}
