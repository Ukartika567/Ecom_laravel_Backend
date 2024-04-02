<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    use HasFactory;
    protected $table = 'company_infos';
    protected $fillable = [
        'user_id',
        'companyName',
        'contactPersonName',
        'company_addr',
        'companyEmail',
        'companyPhone',
        'industry',
        'supportHour',
        'support_adrr',
        'businessName',
        'businessType',
        'businessRegNum',
        'taxIdentifyNum',
        'contactname',
        'contactemail',
        'contactmobile',
    ];
}
