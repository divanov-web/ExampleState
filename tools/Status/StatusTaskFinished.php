<?php
/*
 * ittp
 */
namespace aton\tools\Atp\Status;


use aton\tools\Atp\ButtonTaskFinished;
use aton\tools\Soap\Mail\MailService;

class StatusTaskFinished extends AbstractStatus
{
    static public $statusCode = 'TASK_FINISHED';

    public function getNextStatus(): ?AbstractStatus {
        return new StatusTaskUnderReview();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?ButtonTaskFinished {
        //return new Button('Получить ТЗ', $this->getStatusCode());
        return new ButtonTaskFinished('Прикрепить ТЗ стажёра', $this->getStatusCode());
    }

}
