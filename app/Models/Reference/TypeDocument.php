<?php

namespace App\Models\Reference;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDocument extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvjp';

    protected $table = 'ref_type_document';

    protected $guarded = ['id'];

    public function personaldata()
    {
        return $this->belongsTo(PersonalData::class);
    }
}
