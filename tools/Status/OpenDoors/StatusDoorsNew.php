<?php
/**
 * open_doors
 */
namespace aton\tools\Atp\Status\OpenDoors;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusDoorsNew extends AbstractStatus
{
    static public $statusCode = 'DOORS_NEW';
    static public $eventCode = 'OPEN_DOORS_REGISTRATION';
    static public $eventAfterRejectCode = 'OPEN_DOORS_CANCEL_REJECT';

    static public $statusCodeTestFinished = 'DOORS_TEST_FINISHED';

    public function getNextStatus(): ?AbstractStatus {
        $status = new StatusDoorsInvited();
        return $status;
    }

    public function getRejectStatus(): ?AbstractStatus {
        $status = new StatusDoorsNewRejected();
        return $status;
    }

    public function getButton(): ?Button {
        return new Button('Новая', $this->getStatusCode());
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }


}
