<?php
/*
 * atp
 */
namespace aton\tools\Atp\Status\Atp;


use \aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusAtpDocuments extends AbstractStatus
{
    static public $statusCode = 'ATP_DOCUMENTS';
    static public $eventCode = 'ATP_DOCUMENTS';


    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

}
