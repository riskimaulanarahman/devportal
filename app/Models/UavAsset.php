<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UavAsset extends Model
{
    use HasFactory;

    protected $table = 'request_uavasset';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;

    protected $casts = [
        'isActive' => 'boolean',
    ];
}
