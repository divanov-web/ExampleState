<?php
/**
 * open_doors
 */
namespace aton\tools\Atp\Status\Aod;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusAodNew extends AbstractStatus
{
    static public $statusCode = 'AOD_NEW';
    static public $eventCode = 'OPEN_DOORS_REGISTRATION';
    static public $eventAfterRejectCode = 'OPEN_DOORS_CANCEL_REJECT';

    static public $statusCodeTestFinished = 'DOORS_TEST_FINISHED';

    public function getRejectStatus(): ?AbstractStatus {
        $status = new StatusAodNewRejected();
        return $status;
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }


}
