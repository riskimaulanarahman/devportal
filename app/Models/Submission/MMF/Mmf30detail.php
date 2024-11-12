<?php

namespace App\Models\Submission\MMF;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;

class Mmf30detail extends Model
{
    use HasFactory;

    protected $table = 'request_mmf_30_detail';

    protected $guarded = ['id'];
    protected $casts = [
        'mmf30_id' => 'integer',
        'MaterialCode' => 'string',
        'UnitPrice' => 'integer',
        'ExtendedPrice' => 'integer',
        'Qty' => 'integer',
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
