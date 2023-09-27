<?php
/*
 * ittp
 */
namespace aton\tools\Atp\Status;


use \aton\tools\Atp\Button;

class StatusInvited extends AbstractStatus
{
    static public $statusCode = 'INVITED';
    static public $eventCode = 'ITTP_INVITE_VCV';
    static public $eventAfterRejectCode = 'ITTP_CANCEL_REJECT';

    public function getNextStatus(): ?AbstractStatus {
        return new StatusCompleted();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusItInterviewRejected();
    }

    public function getButton(): ?Button {
        return new Button('Пригласить на&nbsp;интервью', $this->getStatusCode());
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }
}
