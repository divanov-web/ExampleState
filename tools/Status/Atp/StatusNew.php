<?php

namespace aton\tools\Atp\Status\Atp;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;

class StatusNew extends AbstractStatus
{
    static public $statusCode = 'ATP_NEW';
    static public $eventCode = 'ATP_REGISTRATION';

    static public $statusCodeTestFinished = 'ATP_TEST_FINISHED';

    public function getNextStatus(): ?AbstractStatus {
        $status = null;
        return $status;
    }

    public function getRejectStatus(): ?AbstractStatus {
        $status = null;
        return $status;
    }

    public function getButton(): ?Button {
        return new Button('Новая', $this->getStatusCode());
    }

    /**
     * Массив с данынми по кнопке для пользователя в кабинете стажёра
     * @return array|null
     */
    public function getUserButton(): ?array
    {
        if(!$this->isActiveDate()) {
            return null;
        }

        return [
            'name' => 'Пройти тест',
            'url' => '/task/atp_/',
        ];
    }
}
