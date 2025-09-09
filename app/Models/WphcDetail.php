<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Submission\Wphc;
class WphcDetail extends Model
{
    use HasFactory;

    protected $table = 'request_wphc_detail';

    protected $guarded = ['id'];

    public static function gitFillableColumns()
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
