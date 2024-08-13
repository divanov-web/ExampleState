<?php

namespace aton\tools\Atp\Status\Atp;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusUserRejected extends AbstractStatus
{
    static public $statusCode = 'ATP_USER_REJECTED';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return null;
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return null;
    }
}
