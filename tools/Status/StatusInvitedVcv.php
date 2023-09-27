<?php
/*
 * atp
 */
namespace aton\tools\Atp\Status;


use \aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusInvitedVcv extends AbstractStatus
{
    static public $statusCode = 'INVITED_VCV';
    static public $eventCode = 'ATP_INVITE_MAIN';
    static public $eventAfterRejectCode = 'ATP_CANCEL_REJECT_MAIN';

    public function getNextStatus(): ?AbstractStatus {
        return null;
    }

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusInterviewRejected();
    }

    public function getButton(): ?Button {
        return new Button('Пригласить на VCV', $this->getStatusCode());
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }

}
