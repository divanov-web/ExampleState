<?php

namespace aton\tools\Atp\AtpException;


class SaveFormAtpException extends AtpException
{
    const ERROR_CODE = 11009; //Ошибка при обработки формы для сохранения заявки

    protected $exception_level = 1; //Критичность исключения

    public function __construct($resultId)
    {
        $errorMsg = "Application can't be saved. ResultId = " .$resultId;
        parent::__construct(
            $errorMsg,
            self::ERROR_CODE
        );
    }
}