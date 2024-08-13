<?php

namespace aton\tools\Atp\Status\Atp;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusTestFinished extends AbstractStatus
{
    static public $statusCode = 'ATP_TEST_FINISHED';
    static public $eventAfterRejectCode = 'ATP_CANCEL_REJECT_MAIN';

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusTestRejected();
    }

    public function getButton(): ?Button {
        return new Button($this->getButtonValue(), $this->getStatusCode());
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }

}
