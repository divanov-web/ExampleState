<?php

namespace aton\tools\Atp\AtpException;


class AddEventAtpException extends AtpException
{
    const ERROR_CODE = 11001; //не получилось добавить актуальное мероприятие

    protected $exception_level = 1; //Критичность исключения

    public function __construct(string $code, int $year)
    {
        $errorMsg = "Can't add atp event event_code = " . $code . " year = " . $year;
        parent::__construct(
            $errorMsg,
            self::ERROR_CODE
        );
    }
}