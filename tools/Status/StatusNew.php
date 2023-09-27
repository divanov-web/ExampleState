<?php

namespace aton\tools\Atp\Status;


use aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusNew extends AbstractStatus
{
    static public $statusCode = 'NEW';
    static public $eventCode = 'ATP_REGISTRATION_MAIN';

    static public $statusCodeTestFinished = 'TEST_FINISHED';

    public function getNextStatus(): ?AbstractStatus {
        $status = null;
        return $status;
    }

    public function getRejectStatus(): ?AbstractStatus {
        $status = null;
        return $status;
    }

    public function getButton(): ?Button {
        return new Button('Новая', $this->getStatusCode());
    }

}
