<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\Submission\Memorandum;
use App\Models\Employee;
use App\Models\ApproverListHistory;

class MemorandumDetail extends Model
{
    use HasFactory;

    protected $table = 'request_memorandum_detail';
    
    protected $guarded = ['id'];
    protected $fillable = [
        'code_id',
        'user_id',
        'req_id',
        'sequence',
        'startContract',
        'endContract',
        'remarks',
        'sysid',
        'superior_id',
        'isActive'
        ];
    protected $casts = [
        'user_id' => 'integer',
        'isMine' => 'integer',
        'code_id' => 'integer',
        'req_id' => 'integer',
        'sequence' => 'integer',
        'isPendingOnMe' => 'integer'
    ];
    public $timestamps = true;
    
    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['approveddoc']);        
        return $fillable;
    }
    public static function getTableName()
    {
        return (new static)->getTable();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function Memorandum()
    {
        return $this->belongsTo(Memorandum::class, 'req_id');
    }
    public function approverlist()
    {
        return $this->hasMany(ApproverListReq::class,'req_id');
    }

    public function approverHistory()
    {
        return $this->hasMany(ApproverListHistory::class,'req_id');
    }
    public function approver()
    {
        return $this->hasMany(approver::class,'req_id');
    }
    public function approvaltype()
    {
        return $this->hasMany(approvaltype::class,'req_id');
    }
    public function code()
    {
        return $this->belongsTo(Code::class, 'code_id');
    }
}