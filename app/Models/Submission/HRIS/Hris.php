<?php

namespace App\Models\Submission\HRIS;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// use App\Models\User;
// use App\Models\Code;
// use App\Models\ApproverListReq;
// use App\Models\ApproverListHistory;

// class Hris extends Model
// {
//     use HasFactory;

//     protected $table = 'request_hris';

//     protected $guarded = ['id'];

//     protected $fillable = [
//         'user_id',
//         'createdby',
//         'employee_id',
//         'requestStatus',
//         'category',
//         'approveddoc',
//         'bu',
//     ];

//     protected $casts = [
//         'user_id' => 'integer',
//         'createdby' => 'integer',
//         'employee_id' => 'integer',
//         'requestStatus' => 'integer',
//     ];

//     public static function getFillableColumns()
//     {
//         $fillable = (new static)->fillable;
//         $fillable = array_diff($fillable, [
//             'approveddoc'
//         ]);
//         return $fillable;
//     }

//     public static function getTableName()
//     {
//         return (new static)->getTable();
//     }

//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }

//     public function approverlist()
//     {
//         return $this->hasMany(ApproverListReq::class,'req_id');
//     }

//     public function approverHistory()
//     {
//         return $this->hasMany(ApproverListHistory::class,'req_id');
//     }

//     public function code()
//     {
//         return $this->belongsTo(Code::class);
//     }

//     public function detailAdvance()
//     {
//         return $this->belongsTo('App\Models\Submission\Financial\Advance\Advance','id','req_id');
//     }

    

// }
