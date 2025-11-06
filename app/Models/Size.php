<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvjp';

    protected $table = 'personal_size';

    protected $guarded = ['id'];


    public function personaldata()
    {
        return $this->belongsTo(PersonalData::class);
    }
}
