<?php

namespace App\Models\Module;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Headcounts extends Model
{
    use HasFactory;

    protected $table = 'tbl_hc';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'department_id' => 'integer',
    ];
}
