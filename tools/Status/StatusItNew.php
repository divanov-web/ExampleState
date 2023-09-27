<?php
/**
 * ittp
 */
namespace aton\tools\Atp\Status;


use aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

class StatusItNew extends AbstractStatus
{
    static public $statusCode = 'IT_NEW';
    static public $eventCode = 'ITTP_REGISTRATION';
    static public $eventAfterRejectCode = 'ITTP_CANCEL_REJECT';

    public function getNextStatus(): ?AbstractStatus {
        $status = new StatusTaskSent();
        return $status;
    }

    public function getRejectStatus(): ?AbstractStatus {
        $status = new StatusItNewRejected();
        return $status;
    }

    public function getButton(): ?Button {
        return new Button('Новая', $this->getStatusCode());
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ', $this->getStatusCode());
    }


}
