<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
    use HasFactory;

    protected $table = 'reference.tbl_developer';

    protected $fillable = [
        'user_id',
        'developerName',
        'role',
    ];
}
