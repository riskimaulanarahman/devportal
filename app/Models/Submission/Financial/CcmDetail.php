<?php

namespace App\Models\Submission\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CcmDetail extends Model
{
    use HasFactory;

    protected $table = 'request_ccm_detail';

    protected $guarded = ['id'];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, []);
        return $fillable;
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }

}
