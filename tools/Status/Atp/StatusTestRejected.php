<?php

namespace aton\tools\Atp\Status\Atp;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusTestRejected extends AbstractStatus
{
    static public $statusCode = 'ATP_TEST_REJECTED';
    static public $eventCode = 'ATP_REJECTED_MAIN';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusTestFinished();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button($this->getButtonValue(), $this->getStatusCode());
    }

}
