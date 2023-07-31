<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;


trait HasMessage {

    /**
     * @param Request $request
     * @return $this|false|string
     */
    public function getMessage() {

        $message = [
            'show' => 'The data is being displayed.',
            'store' => 'Data has been successfully added.',
            'update' => 'The data was successfully changed.',
            'destroy' => 'Data was successfully deleted.',
            'errordestroysubmission' => 'This data has a relation with others or data does not belong to you, that is still being used. You are not allowed to delete it.',
            'erroruploadimages' => 'Invalid file. Please upload a valid image file.',
            'errornotfound' => 'Data Not Found.',
            'modulenotfound' => 'Module Not Found.'
        ];

        return $message;

    }

    public function mailMessage() {

        $message = [
            'approved' => 'Your submission has been approved and will be processed further.',
            'rejected' => 'We are sorry to inform you that your submission has been rejected. Please check your submission again and reapply if necessary.',
            'reworked' => 'Your submission requires improvement and needs to be fixed according to the notes that have been given.',
            'cancelled' => 'We regret to inform you that your submission has been cancelled.',
            'waitingapproval' => 'Please review the submission and take appropriate action.',
        ];

        return $message;

    }

}