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
    static public $statusCode = 'TASK_SENT';
    static public $eventCode = 'ITTP_SEND_TASK';

    public function getNextStatus(): ?AbstractStatus {
        return new StatusTaskFinished();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button('Отправить ТЗ', $this->getStatusCode());
    }

    public function sendNormalEmail() {
        $atpFile = $this->application->getAtpFileId();
        $arFiles = [];
        $dbItems = ListAtpFilesTable::getList([
            'select' => ['*'],
            'filter' => ['ID' => $atpFile]
        ]);
        while($arItem = $dbItems->fetch()) {
            $arFiles[] = $arItem['UF_FILE'];
        }

        $mailService = new MailService([
                'NAME' => $this->application->getOnlyName(),
                'EMAIL' => $this->application->getEmail(),
                'APP_FILE_ID' => $atpFile,
                'YEAR' => $this->application->getYear()
            ],
            'cr'
        );
        $mailService->sendDefaultMail(static::$eventCode, null, null, null, null, $arFiles);
    }
}
