<?php
/*
 * atp
 */
namespace aton\tools\Atp\Status\Atp;


use \aton\tools\Atp\Button;
use aton\tools\Atp\ButtonOffer;
use aton\tools\Atp\ButtonTask;
use aton\tools\Atp\Status\AbstractStatus;
use aton\tools\HLTables\ListAtpFilesTable;
use aton\tools\Soap\Mail\MailService;

class StatusAtpOffer extends AbstractStatus
{
    static public $statusCode = 'ATP_OFFER';
    static public $eventCode = 'ATP_OFFER';

    static public $eventAfterRejectCode = 'ATP_CANCEL_REJECT_MAIN';

    public function getRejectStatus(): ?AbstractStatus {
        return new StatusAtpOfferReject();
    }

    public function getButton(): ?Button {
        return new ButtonOffer($this->getButtonValue(), $this->getStatusCode());
    }

    public function getButtonAfterReject(): ?Button {
        return new Button('Отменить отказ стажёра', $this->getStatusCode());
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
        $offerFileId = $this->application->getOfferFileId();
        $mailService = new MailService([
            'NAME' => $this->application->getName(),
            'EMAIL' => $this->application->getEmail(),
            'YEAR' => $this->application->getYear()
        ],
            'cr'
        );
        $mailService->sendDefaultMail(static::$eventCode, null, null, null, null, [$offerFileId]);
    }

}
