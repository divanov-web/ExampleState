<?php
/*
 * atp
 */
namespace aton\tools\Atp\Status\Atp;


use \aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusAtpBossInterview extends AbstractStatus
{
    static public $statusCode = 'ATP_BOSS_INTERVIEW';
    static public $eventCode = 'ATP_INVITE_INTERVIEW';

    static public $eventAfterRejectCode = 'ATP_CANCEL_REJECT_MAIN';

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusAtpBossInterviewReject();
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }

}
