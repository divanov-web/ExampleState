<?php
/**
 * ittp
 */
namespace aton\tools\Atp\Status;


use \aton\tools\Atp\Button;

class StatusCompleted extends AbstractStatus
{
    static public $statusCode = 'COMPLETED';
    static public $eventCode = 'ITTP_INVITE_FINAL';

    public function getNextStatus(): ?AbstractStatus {
        return null;
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Приглашение на&nbsp;стажировку', $this->getStatusCode());
    }
}
