<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\Categoryhrsc;

class Hrsc extends Model
{
    use HasFactory;

    protected $table = 'request_hrsc';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'hrsc_category_id',
        'description',
        'priority',
        'completeddate',
        'ticketStatus',
        'confirmationStatus',
        'confirmationRemarks',
        'bu',
        'sector',
        'location',
    ];

    protected $casts = [
        'completeddate' => 'date',
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['priority','completeddate','ticketStatus','codeno','confirmationStatus','confirmationRemarks']);
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

    public function category()
    {
        return $this->belongsTo(Categoryhrsc::class,'hrsc_category_id');
    }

}
