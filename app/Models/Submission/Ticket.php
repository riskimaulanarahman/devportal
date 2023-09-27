<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Code;
use App\Models\Project;
use App\Models\ApproverListReq;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'request_ticket';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'nameSystem',
        'category',
        'codeno',
        'description',
        'priority',
        'completeddate',
        'ticketStatus'
    ];

    protected $casts = [
        'completeddate' => 'date',
    ];

    public static function getFillableColumns()
    {
        // return (new static)->fillable;
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['priority','completeddate','ticketStatus','codeno','nameSystem']);
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

    public function project()
    {
        return $this->belongsTo(Project::class,'nameSystem');
    }

}
