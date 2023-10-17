<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConditionForm extends Model
{
    use HasFactory;

    protected $table = 'tbl_conditionform';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
