<?php

namespace aton\tools\Atp\AtpException;


class DuplicateAppAtpException extends AtpException
{
    const ERROR_CODE = 11003; //Попытка сохранить вторую анкету на мероприятие

    protected $exception_level = 1; //Критичность исключения

    public function __construct(array $data = [])
    {
        $errorMsg = "Duplicated application eventId = " . $data['EVENT_ID'] . " userId = " . $data['USER_ID'] . " resuldId=" . $data['RESULT_ID'];
        parent::__construct(
            $errorMsg,
            self::ERROR_CODE
        );
    }
}