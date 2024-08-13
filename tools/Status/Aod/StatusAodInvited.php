<?php
/**
 * open_doors
 */
namespace aton\tools\Atp\Status\Aod;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusAodInvited extends AbstractStatus
{
    static public $statusCode = 'AOD_INVITED';
    static public $eventCode = 'OPEN_DOORS_INVITE';

    static public $statusCodeTestFinished = 'DOORS_TEST_FINISHED';

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

}
