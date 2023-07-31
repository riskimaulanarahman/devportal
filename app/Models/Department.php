<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'tbl_department';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

    protected $casts = [
        'isUsed' => 'boolean',
    ];

}
