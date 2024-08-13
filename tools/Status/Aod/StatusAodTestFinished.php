<?php
/**
 * open_doors
 */
namespace aton\tools\Atp\Status\Aod;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusAodTestFinished extends AbstractStatus
{
    static public $statusCode = 'AOD_TEST_FINISHED';
    //static public $eventCode = 'OPEN_DOORS_INVITE';

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
