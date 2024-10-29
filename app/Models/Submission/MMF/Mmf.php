<?php

namespace App\Models\Submission\MMF;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;

class Mmf extends Model
{
    use HasFactory;

    protected $table = 'request_mmf';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'employee_id',
        'requestStatus',
        'category',
        'approveddoc',
        'bu',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, [
            'approveddoc'
        ]);
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

    public function approverHistory()
    {
        return $this->hasMany(ApproverListHistory::class,'req_id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

    public function detail28()
    {
        return $this->belongsTo('App\Models\Submission\MMF\Mmf28','id','req_id');
    }

    public function detail30()
    {
        return $this->belongsTo('App\Models\Submission\MMF\Mmf30','id','req_id');
    }
    

}
