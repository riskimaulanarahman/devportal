<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApproverNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $approverData;
    public $listApprover;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($approverData, $listApprover)
    {
        $this->approverData = $approverData;
        $this->listApprover = $listApprover;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('view.name');
        return $this->view('emails.approver_notification')
                    ->subject('Approver Notification')
                    ->with('approverData', $this->approverData)
                    ->with('listApprover', $this->listApprover);
    }
}
