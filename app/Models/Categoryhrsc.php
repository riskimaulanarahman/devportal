<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoryhrsc extends Model
{
    use HasFactory;

    protected $table = 'tbl_hrsc_category';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;
}
