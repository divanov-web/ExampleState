<?php

namespace aton\tools\Atp\Status;


use \aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusTestRejected extends AbstractStatus
{
    static public $statusCode = 'TEST_REJECTED';
    static public $eventCode = 'ATP_REJECTED_MAIN';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusTestFinished();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Отказ', $this->getStatusCode());
    }

}
