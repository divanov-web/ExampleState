<?php

namespace aton\tools\Atp\Status\Atp;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusNewRejected extends AbstractStatus
{
    static public $statusCode = 'ATP_NEW_REJECTED';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusNew();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button($this->getButtonValue(), $this->getStatusCode());
    }
}
