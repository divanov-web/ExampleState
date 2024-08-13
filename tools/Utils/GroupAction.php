<?php

namespace aton\tools\Atp\Utils;

use aton\tools\Atp\AtpApplication;
use aton\tools\Atp\Status\Atp\StatusTestFinished;
use aton\tools\Atp\Status\Atp\StatusTestRejected;
use aton\tools\Tables\Atp\AtpApplicationTable;

class GroupAction
{

    /**
     * Массовый отказ стажёрам в программе после тестирования
     * @param string $eventType
     * @param int $year
     * @param int $maxScore - количество набранных процентов за тест, до достижения которого отправляется отказ
     * @param int|null $minScore
     * @return void
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    static public function rejectAllTasksAtp(string $eventType, int $year, int $maxScore = null, int $minScore = null)
    {
        if($maxScore > 70 || $maxScore == null) {
            throw new \Exception('Подозрительный диапазон максимальной оценки');
        }

        if($eventType != 'ATP') {
            throw new \Exception('Работает только для ATP');
        }

        $filter = [
            'EVENT_TYPE' => $eventType,
            'EVENT_YEAR' => $year,
            '<=ATTEMPT_SCORE' => $maxScore * 2,
            'STATUS_CODE' => StatusTestFinished::$statusCode
        ];
        if($minScore > 0) {
            $filter['>=ATTEMPT_SCORE'] = $minScore * 2;
        }

        $dbItems = AtpApplicationTable::getList([
            'order' => ['ID' => 'DESC'],
            'select' => ['*',
                'EVENT_YEAR' => 'EVENT.YEAR',
                'EVENT_TYPE' => 'EVENT.TYPE',
                'STATUS_CODE' => 'STATUS.CODE',
                'STATUS_VALUE' => 'STATUS.VALUE',
                'ATTEMPT_SCORE' => 'ATTEMPT.SCORE'
            ],
            'filter' => $filter,
        ]);
        while($arItem = $dbItems->fetch()) {
            $application = AtpApplication::getById($arItem['ID']);
            $application->changeStatusByCode(StatusTestRejected::$statusCode);
            $application->save();
            $application->sendEmail();
        }
    }
}