<?php

namespace aton\tools\Atp\AtpException;

use aton\tools\ExceptionLog\AtonException;

abstract class AtpException extends AtonException
{
    // файл для записи лога ошибок
    const LOG_FILE = 'atp_exception.txt';

    const ERROR_CODE = 11000; //Общая ошибка

    protected $exception_level = 1; //Критичность исключения
}