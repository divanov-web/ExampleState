<?php
/**
 * Утилиты для работы со статусами
 */

namespace aton\tools\Atp\Utils;

use aton\tools\Atp\Status\Atp\StatusNew;
use aton\tools\Atp\Status\Aod\StatusAodNew;
use aton\tools\Atp\Status\StatusItNew;

class Status
{
    /**
     * Возвращает код первого статуса в зависимости от типа стажировки
     * @param string $eventType
     * @return string|null
     */
    static public function getFirstStatusCode(string $eventType): ?string
    {
        if($eventType == 'ATP') {
            $status = StatusNew::$statusCode;
        } elseif($eventType == 'ITTP') {
            $status = StatusItNew::$statusCode;
        } elseif($eventType == 'OPEN_DOORS') {
            $status = StatusAodNew::$statusCode;
        } else {
            $status = null;
        }
        return $status;
    }

}
