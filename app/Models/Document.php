<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Reference\TypeDocument;

class Document extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrvjp';

    protected $table = 'personal_document';

    protected $guarded = ['id'];


    public function personaldata()
    {
        return $this->belongsTo(PersonalData::class);
    }

    public function typeDocument()
    {
        return $this->belongsTo(TypeDocument::class);
    }
}
