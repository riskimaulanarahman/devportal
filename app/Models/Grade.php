<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $table = 'tbl_grade';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
