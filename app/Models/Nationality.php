<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    use HasFactory;

    protected $table = 'tbl_nationality';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
