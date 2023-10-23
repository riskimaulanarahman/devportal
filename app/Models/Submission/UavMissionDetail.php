<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
// use App\Models\Project;
use App\Models\ApproverListReq;

class UavMissionDetail extends Model
{
    use HasFactory;

    protected $table = 'request_uavmissiondetail';

    protected $guarded = ['id'];

    protected $fillable = [
        'module_id',
        'req_id',
        'mission_type',
        'missiondate',
        'location_type',
        'location_sector',
        'location_nocompt',
        'mission_pic',
        'device_id',
        'plan_start',
        'plan_end',
        'completed_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'missiondate' => 'date',
    ];

    public static function getFillableColumns()
    {
        // return (new static)->fillable;
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['device_id','plan_start','plan_end','completed_date','status','remarks']);
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

    // public function project()
    // {
    //     return $this->belongsTo(Project::class,'nameSystem');
    // }

}
