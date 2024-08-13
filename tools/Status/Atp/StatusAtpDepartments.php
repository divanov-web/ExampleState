<?php
/*
 * atp
 */
namespace aton\tools\Atp\Status\Atp;


use \aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusAtpDepartments extends AbstractStatus
{
    static public $statusCode = 'ATP_DEPARTMENTS';
    static public $eventCode = 'ATP_DEPARTMENT_CHOICE';
    static public $eventAfterRejectCode = 'ATP_CANCEL_REJECT_MAIN';

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusAtpClassesRejected();
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }

}
