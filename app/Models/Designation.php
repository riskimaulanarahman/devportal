<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $table = 'tbl_designation';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

}
