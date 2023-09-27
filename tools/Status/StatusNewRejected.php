<?php

namespace aton\tools\Atp\Status;


use aton\tools\Atp\Button;

class StatusNewRejected extends AbstractStatus
{
    static public $statusCode = 'NEW_REJECTED';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusNew();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Отказ', $this->getStatusCode());
    }
}
