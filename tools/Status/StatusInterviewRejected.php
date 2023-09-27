<?php

namespace aton\tools\Atp\Status;


use aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusInterviewRejected extends AbstractStatus
{
    static public $statusCode = 'INTERVIEW_REJECTED';
    static public $eventCode = 'ATP_REJECTED_MAIN';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        if($this->application->getEventType() == 'ITTP') {
            $status = new StatusInvited();
        } else {
            $status = new StatusInvitedVcv();
        }
        return $status;
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Отказ', $this->getStatusCode());
    }

}
