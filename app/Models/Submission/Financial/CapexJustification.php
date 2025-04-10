<?php

namespace App\Models\Submission\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapexJustification extends Model
{
    use HasFactory;

    protected $table = 'request_capex_justification';

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
