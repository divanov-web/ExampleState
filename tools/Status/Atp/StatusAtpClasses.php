<?php
/*
 * atp
 */
namespace aton\tools\Atp\Status\Atp;


use \aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusAtpClasses extends AbstractStatus
{
    static public $statusCode = 'ATP_CLASSES';
    static public $eventCode = 'ATP_CLASSES';

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

}
