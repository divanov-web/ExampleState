<?php

namespace aton\tools\Atp\Status;


use \aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusTaskRejected extends AbstractStatus
{
    static public $statusCode = 'IT_TASK_REJECTED';
    static public $eventCode = 'ITTP_REJECT';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusTaskFinished();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Отказ ТЗ', $this->getStatusCode());
    }

}
