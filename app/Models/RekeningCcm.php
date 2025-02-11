<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekeningCcm extends Model
{
    use HasFactory;

    protected $table = 'reference.tbl_rekening_ccm';

    protected $guarded = ['id'];
    

}
