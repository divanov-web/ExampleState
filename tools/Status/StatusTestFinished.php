<?php

namespace aton\tools\Atp\Status;


use \aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusTestFinished extends AbstractStatus
{
    static public $statusCode = 'TEST_FINISHED';
    static public $eventAfterRejectCode = 'ATP_CANCEL_REJECT_MAIN';

    public function getNextStatus(): ?AbstractStatus {
        return new StatusInvitedVcv();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusTestRejected();
    }

    public function getButton(): ?Button {
        return new Button('Получен тест', $this->getStatusCode());
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }

}
