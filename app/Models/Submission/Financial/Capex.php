<?php

namespace App\Models\Submission\Financial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;

class Capex extends Model
{
    use HasFactory;

    protected $table = 'request_capex';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'title',
        'bu',
        'sector',
        'form_type',
        'business_type',
        'project_type',
        'request_type',
        'reason_unbudgeted',
        'approved_budget',
        'currency',
        'equipment',
        'cost_center',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['equipment','cost_center','reason_unbudgeted']);
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
