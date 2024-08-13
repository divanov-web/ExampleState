<?php
/**
 * open_doors
 */
namespace aton\tools\Atp\Status\Aod;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusAodNewRejected extends AbstractStatus
{
    static public $statusCode = 'AOD_NEW_REJECTED';
    static public $eventCode = 'OPEN_DOORS_REJECT';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusAodNew();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

}
