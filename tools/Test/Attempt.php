<?php

namespace aton\tools\Atp\Test;

use aton\tools\Atp\AtpApplication;
use \aton\tools\Atp\AtpEventController;
use aton\tools\Atp\Status\StatusTestFinished;

class Attempt
{
    private $attemptId = 0;

    public function __construct(int $attemptId)
    {
        $this->attemptId = $attemptId;
    }

    /**
     * Меняет статус анкеты на "тест завершён" и добавляем информацию в анкету из попытки тестирования
     * @param string $eventCode
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     * @throws \aton\tools\Atp\AtpException\NotFoundAppAtpException
     * @throws \aton\tools\Atp\AtpException\NotFoundStatusAtpException
     */
    public function finish(string $eventCode = '') {
        $application = AtpApplication::getByUserEvent($eventCode);
        $codeTestFinished = $application->getStatusCodeTestFinished();
        $application->changeStatusByCode($codeTestFinished);
        $application->changeAttempt($this->attemptId);
        $application->save();
    }
}
