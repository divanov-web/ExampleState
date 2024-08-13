<?php
/*
 * ittp
 */
namespace aton\tools\Atp\Status;


use aton\tools\Atp\Button;
use aton\tools\Atp\ButtonTaskFinished;
use aton\tools\Soap\Mail\MailService;

class StatusTaskFinished extends AbstractStatus
{
    static public $statusCode = 'IT_TASK_FINISHED';

    public function getNextStatus(): ?AbstractStatus {
        return new StatusTaskUnderReview();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }

}
