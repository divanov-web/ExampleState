<?php
/*
 * atp
 */
namespace aton\tools\Atp\Status\Atp;


use \aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusInvitedVcv extends AbstractStatus
{
    static public $statusCode = 'ATP_INVITED_VCV';
    static public $eventCode = 'ATP_INVITE_VCV';
    static public $eventAfterRejectCode = 'ATP_CANCEL_REJECT_MAIN';

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusInterviewRejected();
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }

}
