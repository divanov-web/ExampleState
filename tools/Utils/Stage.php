<?php

namespace aton\tools\Atp\Utils;

use aton\tools\Tables\Atp\AtpStageTable;
use aton\tools\Tables\Atp\AtpStatusTable;

class Stage
{
    /**
     * Активна ли регистрация на Стажировку
     * @param string $type
     * @return bool|null
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    static public function isActiveRegister(string $type): ?bool
    {
        $code = self::getFirstStageByEventType($type);
        if(!$code) {
            return null;
        }

        $stage = \aton\tools\Atp\Stage\Stage::getStageByCode($code);
        if(!$stage) {
            return null;
        }

        return $stage->isActiveDate();
    }

    /**
     * Возвращает код первой стадии стажировки по коду стажировки
     * @param String $type
     * @return string|null
     */
    static public function getFirstStageByEventType(String $type): ?string
    {
        $code = null;
        if ($type == 'ATP') {
            $code = 'ATP_NEW';
        } elseif ($type == 'ITTP') {
            $code = 'ITTP_NEW';
        } else if ($type == 'OPEN_DOORS') {
            $code = 'AOD_NEW';
        }
        return $code;
    }

    /**
     * Возвращает код стадии стажировки тестирования/тестового задания по коду стажировки
     * @param String $type
     * @return string|null
     */
    static public function getTaskStageByEventType(String $type): ?string
    {
        $code = null;
        if ($type == 'ATP') {
            $code = 'ATP_TEST';
        } elseif ($type == 'ITTP') {
            $code = 'ITTP_TASK';
        } else if ($type == 'OPEN_DOORS') {
            $code = 'AOD_TEST';
        }
        return $code;
    }

    /**
     * Убирает несоответсвие старых и новых кодов стажировок (сейчас только для AOD вместо OPEN_DOORS)
     * @param String $type
     * @return string|null
     */
    static public function getProgramCodeByType(String $type): ?string
    {
        if($type == 'OPEN_DOORS') {
            $type = 'AOD';
        }
        return $type;
    }
}
