<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Code;

use Storage;
use DB;
// use Barryvdh\DomPDF\Facade as PDF;

class SubmissionMail extends Mailable
{
    use Queueable, SerializesModels;
    public $mailData;
    public $code;
    // public $details;
    // public $text;
    // public $final;
    // public $file;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        // $this->to=$to;
        $this->mailData=$mailData;

        $code = Code::findOrFail($mailData['submission']->code_id);
        $this->code = $code->code;
        // $this->details=$details;
        // $this->text=$text;
        // $this->final=$final;
        // $this->file=$mailData->approveddoc;
        // $this->form=$form;

        // $roletype = DB::table('top_role')
        // ->where('formid',$form)
        // ->get();

        // $this->roletype=$roletype;
        
        // $Mailrecipient = DB::table('tbl_mailrecipient')
        // ->where('formid',$form)
        // ->where('isActive',1)
        // ->get();

        // if($final == 1) {
        //     $file = $this->generatePDF($mailData->id);
        //     foreach ($Mailrecipient as $cc){
        //         $this->cc($cc->email);
        //     }
        //     $this->attach(storage_path('app/public/pdf/'.$file));

        // }
    }

    public function build()
    {  
        $subject = 'Submission - '.$this->code;
        return $this->subject($subject)->view('emails.projectrequestmail');
    }
}
