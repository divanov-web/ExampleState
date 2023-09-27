<?php

namespace aton\tools\Atp\AtpException;


class NotFoundStatusAtpException extends AtpException
{
    const ERROR_CODE = 11005; //Не найдет статус по коду

    protected $exception_level = 1; //Критичность исключения

    public function __construct($statusCode)
    {
        $errorMsg = "Status not found statusCode = " .$statusCode . "";
        parent::__construct(
            $errorMsg,
            self::ERROR_CODE
        );
    }
}