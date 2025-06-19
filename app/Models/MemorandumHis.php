<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemorandumHis extends Model
{
    use HasFactory;

    protected $table = 'request_memorandum_his';
    
    protected $guarded = ['id'];
    
    public $timestamps = false;
    
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
        return $this->belongsTo('App\Models\Submission\Memorandum', 'sys_id', 'sys_id');
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