<?php

namespace App\Models\Submission\Financial\Advance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceDetail extends Model
{
    use HasFactory;

    protected $table = 'request_f_advance_detail';

    protected $guarded = ['id'];
    protected $casts = [
        'advance_id' => 'integer',
        'Amount' => 'integer',
    ];
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
