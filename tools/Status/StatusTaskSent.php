<?php
/**
 * ittp
 */
namespace aton\tools\Atp\Status;


use \aton\tools\Atp\Button;
use aton\tools\Atp\ButtonTask;
use aton\tools\HLTables\ListAtpFilesTable;
use aton\tools\Soap\Mail\MailService;

class StatusTaskSent extends AbstractStatus
{
    static public $statusCode = 'IT_TASK_SENT';
    static public $eventCode = 'ITTP_SEND_TASK';

    public function getNextStatus(): ?AbstractStatus {
        return null;
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new ButtonTask('Отправть ТЗ', $this->getStatusCode());
    }

    public function sendNormalEmail(): void
    {
        $files = $this->application->getSelectedFiles();
        $arFiles = [];
        $dbItems = ListAtpFilesTable::getList([
            'select' => ['*'],
            'filter' => ['ID' => $files]
        ]);
        while($arItem = $dbItems->fetch()) {
            $arFiles[] = $arItem['UF_FILE'];
        }

        $mailService = new MailService([
                'NAME' => $this->application->getName(),
                'EMAIL' => $this->application->getEmail(),
                'YEAR' => $this->application->getYear()
            ],
            'cr'
        );
        $mailService->sendDefaultMail(static::$eventCode, null, null, null, null, $arFiles);
    }
}
