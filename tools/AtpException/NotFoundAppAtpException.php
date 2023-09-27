<?php

namespace aton\tools\Atp\AtpException;


class NotFoundAppAtpException extends AtpException
{
    const ERROR_CODE = 11004; //Не найдена заявка в БД по id

    protected $exception_level = 1; //Критичность исключения

    public function __construct($id)
    {
        $errorMsg = "application not found Id = " .$id . "";
        parent::__construct(
            $errorMsg,
            self::ERROR_CODE
        );
    }
}