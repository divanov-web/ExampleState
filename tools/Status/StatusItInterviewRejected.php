<?php
/**
 * ittp
 */
namespace aton\tools\Atp\Status;


use aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusItInterviewRejected extends AbstractStatus
{
    static public $statusCode = 'IT_INTERVIEW_REJECTED';
    static public $eventCode = 'ITTP_REJECT';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusInvited();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Отказ', $this->getStatusCode());
    }

}
