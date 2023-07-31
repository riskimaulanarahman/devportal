<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use HasFactory;

    protected $table = 'sequences';
    
    protected $guarded = ['id'];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
    ];
}
