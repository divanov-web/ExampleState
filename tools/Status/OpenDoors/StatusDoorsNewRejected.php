<?php
/**
 * open_doors
 */
namespace aton\tools\Atp\Status\OpenDoors;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusDoorsNewRejected extends AbstractStatus
{
    static public $statusCode = 'DOORS_NEW_REJECTED';
    static public $eventCode = 'OPEN_DOORS_REJECT';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusDoorsNew();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Отказ', $this->getStatusCode());
    }

}
