<?php

namespace App\Models\Submission\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;

class Ccm extends Model
{
    use HasFactory;

    protected $table = 'request_ccm';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'title',
        'bu',
        'sector',
        'request_type',
        'intermediary',
        'payment_from',
        'payment_to',
        'payment_to_currency',
        'payment_to_amount',
        'remarks',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['sector','remarks','intermediary']);
        return $fillable;
    }

    public static function getTableName()
    {
        return (new static)->getTable();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approverlist()
    {
        return $this->hasMany(ApproverListReq::class,'req_id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

}
