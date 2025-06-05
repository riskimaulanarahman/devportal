<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rfc extends Model
{
    use HasFactory;
    protected $table = 'archive._tbl_rfc';
    protected $guarded = ['id'];
}