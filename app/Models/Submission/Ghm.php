<?php

namespace App\Models\Submission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Employee;
use App\Models\Code;
use App\Models\ApproverListReq;
use App\Models\Ghm_room;

class Ghm extends Model
{
    use HasFactory;

    protected $table = 'request_ghm';

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'requestStatus',
        'ghm_room_id',
        'employee_id',
        'description',
        'bu',
        'sector',
        'text',
        'guest',
        'family',
        'ticketStatus',
        'confirmationStatus',
        'confirmationRemarks',
        'startDate',
        'endDate'
    ];
    protected $dates = [
        'startDate',
        'endDate',
    ];

    protected $casts = [
        'completeddate' => 'date',
        'ghm_room_id' => 'integer',
        'employee_id' => 'array',
        // 'startDate' => 'date',
        // 'endDate' => 'date'
    ];

    public static function getFillableColumns()
    {
        $fillable = (new static)->fillable;
        $fillable = array_diff($fillable, ['completeddate','ticketStatus','codeno','confirmationStatus','confirmationRemarks']);
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
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    // Accessor to deserialize employee_id from XML to array
    // public function getEmployeeIdAttribute($value)
    // {
    //     return $this->deserializeFromXML($value);
    // }

    // // Mutator to serialize employee_id from array to XML
    // public function setEmployeeIdAttribute($value)
    // {
    //     $this->attributes['employee_id'] = $this->serializeToXML($value);
    // }

    // // Method to serialize array to XML
    // private function serializeToXML($employeeIds)
    // {
    //     $xmlString = '<Employees>';
    //     foreach ($employeeIds as $id) {
    //         $xmlString .= "<EmployeeId>{$id}</EmployeeId>";
    //     }
    //     $xmlString .= '</Employees>';
    //     return $xmlString;
    // }

    // // Method to deserialize XML to array
    // private function deserializeFromXML($xmlString)
    // {
    //     $employeeIds = [];
    //     $xml = simplexml_load_string($xmlString);
    //     foreach ($xml->EmployeeId as $id) {
    //         $employeeIds[] = (int) $id;
    //     }
    //     return $employeeIds;
    // }

    public function approverlist()
    {
        return $this->hasMany(ApproverListReq::class,'req_id');
    }

    public function code()
    {
        return $this->belongsTo(Code::class);
    }

    public function ghm_room()
    {
        return $this->belongsTo(Ghm_room::class,'gh_room_id');
    }

}
