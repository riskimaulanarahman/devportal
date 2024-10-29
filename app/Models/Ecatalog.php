<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Ecatalog extends Model
{
    use HasFactory;

    protected $table = 'reference.tbl_ecatalog';

    protected $guarded = ['id'];

    protected $casts = [
        'historicalPrice' => 'integer',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
        ]);
        return $fillable;
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }
    

}
