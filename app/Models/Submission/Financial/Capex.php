<?php

namespace App\Models\Submission\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;

class Capex extends Model
{
    use HasFactory;

    protected $table = 'request_capex';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'category_id',
        'title',
        'bu',
        'estate',
        'form_type',
        'business_type',
        'project_type',
        'request_type',
        'reason_unbudgeted',
        'approved_budget',
        'additional_budget',
        'equipment',
        'cost_center',
        'additional_approver',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['equipment','cost_center','reason_unbudgeted','additional_approver']);
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

}
