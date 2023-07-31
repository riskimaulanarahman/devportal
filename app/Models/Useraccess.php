<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Useraccess extends Model
{
    use HasFactory;

    protected $table = 'tbl_useraccess';

    protected $guarded = ['id'];
    
    public $timestamps = false;

    protected $casts = [
        'module_id' => 'integer',
        'employee_id' => 'integer',
        'allowAdd' => 'boolean',
        'allowEdit' => 'boolean',
        'allowDelete' => 'boolean',
        'allowView' => 'boolean',
    ];
}
