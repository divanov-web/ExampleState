<?php

namespace aton\tools\Atp\Status\Atp;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;
use aton\tools\Atp\Status\StatusInvited;

class StatusAtpClassesRejected extends AbstractStatus
{
    static public $statusCode = 'ATP_CLASSES_REJECTED';
    static public $eventCode = 'ATP_REJECTED_MAIN';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        $status = new StatusAtpDepartments();
        return $status;
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button($this->getButtonValue(), $this->getStatusCode());
    }

}