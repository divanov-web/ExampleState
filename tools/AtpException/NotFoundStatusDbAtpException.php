<?php

namespace aton\tools\Atp\AtpException;


class NotFoundStatusDbAtpException extends AtpException
{
    const ERROR_CODE = 11008; //Не найдет статус по коду в БД

    protected $exception_level = 1; //Критичность исключения

    public function __construct($statusCode)
    {
        $errorMsg = "Status not found in database statusCode = " .$statusCode . "";
        parent::__construct(
            $errorMsg,
            self::ERROR_CODE
        );
    }
}