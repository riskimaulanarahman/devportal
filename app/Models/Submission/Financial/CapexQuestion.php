<?php

namespace App\Models\Submission\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\refCapexq;


class CapexQuestion extends Model
{
    use HasFactory;

    protected $table = 'request_capex_question';

    protected $guarded = ['id'];

    protected $casts = [
        'question_id' => 'integer',
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

    public function refcapexq()
    {
        return $this->belongsTo(refCapexq::class,'question_id','id');
    }

}
