<?php

namespace aton\tools\Atp\Status;


use aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusItNewRejected extends AbstractStatus
{
    static public $statusCode = 'IT_NEW_REJECTED';
    static public $eventCode = 'ITTP_REJECT_REGISTRATION';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusItNew();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Отказ', $this->getStatusCode());
    }

}
