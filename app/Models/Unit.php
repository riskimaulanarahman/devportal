<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'reference.tbl_unit';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
