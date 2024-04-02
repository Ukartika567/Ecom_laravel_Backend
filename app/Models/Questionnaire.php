<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;
    protected $table = 'questionnaires';
    protected $fillable = [
        'id',
        'resp_ref_id',
        'parent_ques_id',
        'question',
        'ans',
        'questionnaireType',
        'supplier_id',
        'customer_id',
        'quote_id',
        'status',
        'created_at',
        'updated_at'
    ];
}
