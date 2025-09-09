<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\WphcDetail;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;

class Wphc extends Model
{
    use HasFactory;

    protected $table = 'request_wphc';

    protected $guarded = ['id'];

    protected $fillable = [
        'request_status',
        'user_id',
        'employee_id',
        'module_id',
        'approveddoc',
        'superios_id',
        'depthead_id',
        'remarks',
        'created_at',
        'updated_at'
    ];

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

    public function wphc_detail()
    {
        return $this->hasOne(WphcDetail::class, 'req_id', 'id');
    }

}
