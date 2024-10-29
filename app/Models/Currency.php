<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'reference.tbl_currency';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
