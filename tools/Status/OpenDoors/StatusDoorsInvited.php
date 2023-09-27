<?php
/**
 * open_doors
 */
namespace aton\tools\Atp\Status\OpenDoors;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusDoorsInvited extends AbstractStatus
{
    static public $statusCode = 'DOORS_INVITED';
    static public $eventCode = 'OPEN_DOORS_INVITE';

    static public $statusCodeTestFinished = 'DOORS_TEST_FINISHED';

    public function getNextStatus(): ?AbstractStatus {
        return null;
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Пригласить', $this->getStatusCode());
    }

}
