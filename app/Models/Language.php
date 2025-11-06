<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;
    
    protected $connection = 'sqlsrvjp';

    protected $table = 'personal_language';

    protected $guarded = ['id'];


    public function personaldata()
    {
        return $this->belongsTo(PersonalData::class);
    }
}
