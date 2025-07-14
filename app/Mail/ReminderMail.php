<?php

namespace App\Mail;

use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Module;

use Storage;
use DB;
use App\Http\Traits\HasGetModule;
use App\Http\Traits\HasGenerateCode;
// use Barryvdh\DomPDF\Facade as PDF;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels, HasGetModule, HasGenerateCode;
    public $mailData;
    public $modulename;
    public $module;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData,$modulename)
    {
        $appEnv = env('APP_ENV');
        $url = ($appEnv == 'production') ? 'http://172.18.83.38/' : 'http://localhost/';
        $this->module = new Module();
        $this->mailData=$mailData;
        $this->modulename=$modulename;

        // gunakan jika membutuhkan CC email
        // #########################################
        // $MailrecipientNoBu = DB::table('reference.tbl_mailrecipient')
        // ->where('module',$modulename)
        // ->where('isActive',1)
        // ->get();

        // // Base query for mail recipients
        // $queryMr = DB::table('reference.tbl_mailrecipient')
        //     ->where('module', $modulename)
        //     ->where('isActive', 1)
        //     ->where('company_list', 'like', '%' . $mailData['bu'] . '%');

        // // Execute the query and get the results
        // $Mailrecipient = $queryMr->get();

        // foreach ($Mailrecipient as $cc) {
        //     $this->cc($cc->email); // cc
        // }
        // #########################################


    }

    public function build()
    {  
       
        $viewblade = '';

        switch ($this->modulename) {
            case 'Mcop':
                $subject = $this->mailData['bu'].' MCOP Reminder';
                $viewblade = 'emails.HRIS.mcopExpired';
                break;
            default:
                $viewblade = 'emails.defaultmail';
                break;
        }

        return $this->subject($subject)->view($viewblade);
    }
}
