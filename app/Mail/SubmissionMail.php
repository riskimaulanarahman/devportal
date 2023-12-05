<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Code;
use App\Models\Submission\Project;
use App\Models\Submission\Ticket;
use App\Models\Assignmentto;
use App\Models\Stackholders;
use App\Models\Module;
use App\Models\Attachment;
use App\Models\Categoryhrsc;

use Storage;
use DB;
use App\Http\Traits\HasGetModule;
// use Barryvdh\DomPDF\Facade as PDF;

class SubmissionMail extends Mailable
{
    use Queueable, SerializesModels, HasGetModule;
    public $mailData;
    public $modulename;
    public $code;
    public $projectName;
    public $developer;
    public $final;
    public $module;
    public $attachment;
    public $category;
    public $assignment;
    // public $details;
    // public $text;
    // public $final;
    // public $file;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData,$modulename,$final)
    {
        // $this->to=$to;
        $this->module = new Module();
        $this->mailData=$mailData;
        $this->modulename=$modulename;
        $this->final=$final;

        $code = Code::findOrFail($mailData['submission']->code_id);
        $this->code = $code->code;

        $Mailrecipient = DB::table('tbl_mailrecipient')
        ->where('module',$modulename)
        ->where('isActive',1)
        ->get();

        if (!empty($mailData['submission']->category_id)) {
            $category = DB::table('tbl_categoryform')->select('nameCategory')->where('id',$mailData['submission']->category_id)->first();
            $this->category = $category->nameCategory;
        }
        // PROJECT MODULE
        if($modulename == 'Project') {
            $attachments = Attachment::where('req_id',$mailData['submission']->id)
                                ->where('module_id',$this->getModuleId($modulename))
                                ->get();
            $this->attachment = $attachments;
            
            if($final == 1) {
                $stackholders = Stackholders::leftJoin('tbl_employee','tbl_stackholders.employee_id','=','tbl_employee.id')
                                ->leftJoin('users','tbl_employee.LoginName','=','users.username')
                                ->select('tbl_stackholders.*','users.email')
                                ->where('req_id',$mailData['submission']->id)
                                ->where('module_id',$this->getModuleId($modulename))
                                ->get();
                
                foreach ($stackholders as $stacks){
                    $this->cc($stacks->email);
                }
                foreach ($Mailrecipient as $cc){
                    if($cc->company_list == null) {
                        $this->cc($cc->email);
                    } 
                }
            }
        }

        // TICKET MODULE
        if($modulename == 'Ticket') {
            if($mailData['submission']->nameSystem !== null) {
                $project = Project::findOrFail($mailData['submission']->nameSystem);
                $this->projectName = $project->nameSystem;
            } else {
                $this->projectName = 'Others';
            }
            
            

            if($mailData['email'] == 'kf_developer@d1.lcl') {  
                if($project->id == 120 || $project->parentID == 120) {
                    foreach ($Mailrecipient as $cc){
                        if($cc->company_list == 'iop') {
                            $this->cc($cc->email);
                        } 
                    }
                } else if($project->id == 158 || $project->parentID == 158) {
                    foreach ($Mailrecipient as $cc){
                        if($cc->company_list == 'webmap') {
                            $this->cc($cc->email);
                        } 
                    }
                } else {
                    foreach ($Mailrecipient as $cc){
                        if($cc->company_list == null) {
                            $this->cc($cc->email);
                        } 
                    }
                }
            }

            if($final == 1) {
                $developerAssignment = Assignmentto::leftJoin('tbl_developer','tbl_assignment.developer_id','=','tbl_developer.id')
                                        ->leftJoin('users','tbl_developer.user_id','=','users.id')
                                        ->select('tbl_developer.*','users.email')
                                        ->where('req_id',$mailData['submission']->id)
                                        ->where('module_id',$this->getModuleId($modulename))
                                        ->get();
                $this->developer=$developerAssignment;
                foreach ($developerAssignment as $devemail){
                    $this->cc($devemail->email);
                }
            }

        }

        if($modulename == 'Hrsc') {
            $Categoryhrsc = Categoryhrsc::findOrFail($mailData['submission']->hrsc_category_id);
            $this->category = $Categoryhrsc->name;
        }
        // UAV Mission || HRSC MODULE
        if($modulename == 'UavMission' || $modulename == 'Hrsc') {

            if($final == 1) {
                $assignmentdata = Assignmentto::leftJoin('tbl_employee','tbl_assignment.employee_id','=','tbl_employee.id')
                                        ->leftJoin('users','tbl_employee.LoginName','=','users.username')
                                        ->select('tbl_employee.*','users.email')
                                        ->where('req_id',$mailData['submission']->id)
                                        ->where('module_id',$this->getModuleId($modulename))
                                        ->get();
                $this->assignment=$assignmentdata;
                foreach ($assignmentdata as $email){
                    $this->cc($email->email);
                }
            }

        }

        

        // $this->details=$details;
        // $this->text=$text;
        // $this->final=$final;
        // $this->file=$mailData->approveddoc;
        // $this->form=$form;

        // $roletype = DB::table('top_role')
        // ->where('formid',$form)
        // ->get();

        // $this->roletype=$roletype;
        
        // if($final == 1) {
        //     $file = $this->generatePDF($mailData->id);
        //     $this->attach(storage_path('app/public/pdf/'.$file));

        // }
    }

    public function build()
    {  
        $subject = 'Submission - '.$this->code;
        $viewblade = '';

        switch ($this->modulename) {
            case 'Project':
                $viewblade = 'emails.projectrequestmail';
                break;
            case 'Ticket':
                $viewblade = 'emails.ticketrequestmail';
                break;
            case 'UavMission':
                $viewblade = 'emails.uavmissionrequestmail';
                break;
            case 'Hrsc':
                $viewblade = 'emails.hrscrequestmail';
                break;
            default:
                $viewblade = 'emails.defaultmail';
                break;
        }

        return $this->subject($subject)->view($viewblade);
    }
}
