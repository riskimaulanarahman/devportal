<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvaltype extends Model
{
    use HasFactory;

    protected $table = 'tbl_approvaltype';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

    protected $casts = [
        'isactive' => 'boolean',
    ];
}
