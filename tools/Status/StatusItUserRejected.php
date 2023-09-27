<?php

namespace aton\tools\Atp\Status;


use \aton\tools\Atp\Button;

class StatusItUserRejected extends AbstractStatus
{
    static public $statusCode = 'USER_REJECTED';
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
