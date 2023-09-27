<?php
/*
 * ittp
 */
namespace aton\tools\Atp\Status;


use aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusTaskUnderReview extends AbstractStatus
{
    static public $statusCode = 'TASK_UNDER_REVIEW';
    static public $eventAfterRejectCode = 'ITTP_CANCEL_REJECT';

    public function getNextStatus(): ?AbstractStatus {
        return new StatusInvited();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusTaskRejected();
    }

    public function getButton(): ?Button {
        return new Button('ТЗ на проверку', $this->getStatusCode());
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }

}
