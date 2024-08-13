<?php
/*
 * atp
 */
namespace aton\tools\Atp\Status\Atp;


use \aton\tools\Atp\Button;
use aton\tools\Atp\ButtonResult;
use aton\tools\Atp\Status\AbstractStatus;
use aton\tools\Soap\Mail\MailService;
use aton\tools\Utils\Date\Month;

class StatusAtpResult extends AbstractStatus
{
    static public $statusCode = 'ATP_RESULT';
    static public $eventCode = 'ATP_FIRST_DAY';

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new ButtonResult($this->getButtonValue(), $this->getStatusCode());
    }

    /**
     * Заменяем стандартную отправку письма, чтобы прикрепить файл оффера
     * @return void
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     * @throws \aton\tools\Soap\Mail\MailException\EventNotFoundMailException
     * @throws \aton\tools\Soap\Mail\MailException\FieldCheckMailException
     */
    public function sendNormalEmail(): void
    {
        $firstDay = $this->application->getFirstDay();
        $month = Month::getMonthGenitive($firstDay->format('m'), true);
        $stringDay = $firstDay->format('d ' . $month . ' Y г. в H:i');
        $mailService = new MailService([
            'NAME' => $this->application->getName(),
            'EMAIL' => $this->application->getEmail(),
            'YEAR' => $this->application->getYear(),
            'FIRST_DAY' => $stringDay
        ],
            'cr'
        );
        $mailService->sendDefaultMail(static::$eventCode, null, null, null, null);
    }

}
