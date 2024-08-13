<?php
/**
 * open_doors
 */
namespace aton\tools\Atp\Status\Aod;

use aton\tools\Atp\AodCertificate;
use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;
use aton\tools\Soap\Mail\MailService;

class StatusAodCompleted extends AbstractStatus
{
    static public $statusCode = 'AOD_COMPLETED';
    static public $eventCode = 'OPEN_DOORS_COMPLETED';

    public function getNextStatus(): ?AbstractStatus {
        return null;
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
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
        $offerFileId = $this->createCertificate();
        $mailService = new MailService([
            'NAME' => $this->application->getName(),
            'EMAIL' => $this->application->getEmail(),
            'YEAR' => $this->application->getYear()
        ],
            'cr'
        );
        $mailService->sendDefaultMail(static::$eventCode, null, null, null, null, [$offerFileId]);
    }

    private function createCertificate(): int
    {
        $certificate = new AodCertificate($this->application);
        return $certificate->create();
    }

}
