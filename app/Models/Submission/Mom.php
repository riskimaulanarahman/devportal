<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;

class Mom extends Model
{
    use HasFactory;

    protected $table = 'request_mom';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'subjectMeeting',
        'date',
        'chairman',
        'venue',
        'isZoom',
        'chairman_userid',
    ];

    protected $casts = [
        'date' => 'date',
        'user_id' => 'integer',
        'chairman_userid' => 'integer',
    ];

    public static function getFillableColumns()
    {
        // return (new static)->fillable;
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['isZoom','chairman_userid']);
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
