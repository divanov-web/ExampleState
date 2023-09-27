<?php
/**
 * State Pattern
 * Status Classes - State of instance AtpApplication
 */

namespace aton\tools\Atp\Status;


use aton\tools\Atp\AtpApplication;
use aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;

abstract class AbstractStatus
{
    /**
     * @var AtpApplication
     */
    protected $application;

    /**
     * Код статуса в БД
     * @var String
     */
    static public $statusCode = null;

    /**
     * Код статуса "тест пройден". Устанавливается текущему статусу, если на нём есть тестирование и следующий статус "тест пройдён"
     * @var String
     */
    static public $statusCodeTestFinished = null;

    /**
     * Код почтового шаблона, который отправляется стажёру при переходе на этот статус. Если пустое, то письмо не отправляется
     * @var String
     */
    static public $eventCode = null;

    /**
     * Код почтового шаблона, который отправляется стажёру при переходе на этот статус после отказа. Если пустое, то письмо не отправляется
     * @var String
     */
    static public $eventAfterRejectCode = null;

    /**
     * Является ли статус типа отклонён
     * @var bool
     */
    protected $rejectedStatus = false;

    /**
     * @return bool
     */
    public function isRejectedStatus(): bool
    {
        return $this->rejectedStatus;
    }


    public function getStatusCode()
    {
        return static::$statusCode;
    }

    /**
     * Получить код статуса "тест пройден"
     * @return String|null
     */
    public function getStatusCodeTestFinished()
    {
        return static::$statusCodeTestFinished;
    }

    public function setApplication(AtpApplication $application)
    {
        $this->application = $application;
    }

    abstract public function getNextStatus(): ?AbstractStatus;

    abstract public function getRejectStatus(): ?AbstractStatus;

    public function getNextButton()
    {
        if ($this->getNextStatus()) {
            if ($this->isRejectedStatus()) {
                $button = $this->getNextStatus()->getButtonAfterReject();
            } else {
                $button = $this->getNextStatus()->getButton();
            }
            $button->greenColor();
            $button->setApplicationId($this->application->getApplicationId());
            $button->setApplication($this->application);
            return $button->show();
        } else {
            return '';
        }
    }

    public function getRejectButton()
    {
        if ($this->getRejectStatus()) {
            $button = $this->getRejectStatus()->getButton();
            $button->redColor();
            $button->setApplicationId($this->application->getApplicationId());
            return $button->show();
        } else {
            return '';
        }
    }

    /**
     * Кнопка следующего статуса
     * @return Button|null
     */
    public function getButton(): ?Button
    {
        return new Button();
    }

    /**
     * Кнопка следующего статуса, если текущий статус был типа "отказ"
     * По-умолчанию кнопка возвращения статуса выгрлядит как обычная кнопка перехода на статус
     * @return Button|null
     */
    public function getButtonAfterReject(): ?Button
    {
        return $this->getButton();
    }

    /**
     * Определение какое письмо отправить на этом статусе в зависимости
     */
    public function sendEmail()
    {
        if ($this->application->getPreviousStatus() && $this->application->getPreviousStatus()->isRejectedStatus()) {
            $this->sendAfterRejectEmail();
        } else {
            $this->sendNormalEmail();
        }
    }

    /**
     * Отправка почтового шаблона при переходе на этот статус естественным путём (по порядку)
     * Письмо отправляется, только если у статуса есть почтовый шаблон $eventCode
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     * @throws \aton\tools\Soap\Mail\MailException\EventNotFoundMailException
     * @throws \aton\tools\Soap\Mail\MailException\FieldCheckMailException
     */
    public function sendNormalEmail() {
        if(strlen(static::$eventCode)) {
            $mailService = new MailService([
                'NAME' => $this->application->getOnlyName(),
                'EMAIL' => $this->application->getEmail(),
                'YEAR' => $this->application->getYear()
            ],
            'cr');
            $mailService->sendDefaultMail(static::$eventCode);
        }
    }

    /**
     * Отправка почтового шаблона при переходе на этот статус в случае, если был ошибочный отказ
     * Письмо отправляется, только если у статуса есть почтовый шаблон $eventAfterRejectCode
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     * @throws \aton\tools\Soap\Mail\MailException\EventNotFoundMailException
     * @throws \aton\tools\Soap\Mail\MailException\FieldCheckMailException
     */
    public function sendAfterRejectEmail()
    {
        if (strlen(static::$eventAfterRejectCode)) {
            $mailService = new MailService([
                    'NAME' => $this->application->getOnlyName(),
                    'EMAIL' => $this->application->getEmail(),
                    'YEAR' => $this->application->getYear()
                ],
                'cr'
            );
            $mailService->sendDefaultMail(static::$eventAfterRejectCode);
        }
    }

}
