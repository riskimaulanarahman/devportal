<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefCapexq extends Model
{
    use HasFactory;

    protected $table = 'capex_question';
    
    protected $guarded = ['id'];
    
}
