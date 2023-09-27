<?php

namespace aton\tools\Atp\AtpException;


class UpdateApplicationAtpException extends AtpException
{
    const ERROR_CODE = 11007; //не получилось обновить анкету

    protected $exception_level = 1; //Критичность исключения

    public function __construct(array $data = [], $arError = [])
    {
        $errorMsg = "Can't update atp application error: " . implode(', ', $arError) . ". eventId = " . $data['EVENT_ID'] . " userId = " . $data['USER_ID'] . " resuldId=" . $data['RESULT_ID'];
        parent::__construct(
            $errorMsg,
            self::ERROR_CODE
        );
    }
}