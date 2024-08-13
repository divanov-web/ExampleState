<?php
/**
 * Паттерн "Состояние"
 * Классы Status - состояния объекта AtpApplication
 */

namespace aton\tools\Atp\Status;


use aton\tools\Atp\AtpApplication;
use aton\tools\Atp\Button;
use aton\tools\Soap\Mail\MailService;
use aton\tools\Tables\Atp\AtpStatusTable;
use aton\Main\Type\DateTime as DateTime;

abstract class AbstractStatus
{
    /**
     * @var AtpApplication
     */
    protected $application;

    /**
     * Массив данных статуса
     * @var array
     */
    protected $statusData = [];

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
     * Текст на кнопке для админки
     * @var string
     */
    protected string $buttonValue = '';

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

    /**
     * Название для кнопки принятия
     * @return string
     */
    public function getButtonValue()
    {
        return $this->buttonValue;
    }

    /**
     * Получить из базы информацию по статусу
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function __construct()
    {
        $status = AtpStatusTable::getList([
            'select' => [
                '*',
                'NEXT_STATUS_CODE' => 'NEXT_STATUS.CODE',
            ],
            'filter' => ['CODE' => static::$statusCode],
            'cache' => array(
                'ttl' => 60
            )
        ])->fetch();
        if($status) {
            $this->statusData = $status;
            $this->buttonValue = $status['BUTTON_VALUE'] ?: '-Название-';
        }
    }

    public function getNextStatus(): ?AbstractStatus {
        $nextStatus = null;
        if($this->statusData['NEXT_STATUS_CODE']) {
            $nextStatus = AtpApplication::getStatusByCode($this->statusData['NEXT_STATUS_CODE']);
        }
        return $nextStatus;
    }

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
        $buton = null;
        if($this->getButtonValue()) {
            $buton = new Button($this->getButtonValue(), $this->getStatusCode());
        }
        return $buton;
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
     * Массив с данынми по кнопке для пользователя в кабинете стажёра
     * @return array|null
     */
    public function getUserButton(): ?array
    {
        return null;
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
    public function sendNormalEmail(): void {
        if(strlen(static::$eventCode)) {
            $mailService = new MailService([
                'NAME' => $this->application->getName(),
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
                    'NAME' => $this->application->getName(),
                    'EMAIL' => $this->application->getEmail(),
                    'YEAR' => $this->application->getYear()
                ],
                'cr'
            );
            $mailService->sendDefaultMail(static::$eventAfterRejectCode);
        }
    }

    /**
     * Проверяет являются ли даты статуса активными на данный момент
     * @return true
     */
    public function isActiveDate(): bool {
        $data = $this->application->getData();
        /** @var DateTime $dateStart */
        $dateStart = $data['STAGE_DATE_START'] ?? null;
        /** @var DateTime $dateStart */
        $dateEnd = $data['STAGE_DATE_END'] ?? null;
        if($data['STATUS_CODE'] == 'ATP_DEPARTMENTS') { //Исключение. Этой даты нет в списке этапов, поэтому пришлось сделать костыль. Нужно в следующем году согалсовать переделку с кадрами
            $dateEnd->add('+3 days');
        }
        $now = new DateTime();

        if ($dateStart === null || $dateEnd === null) {
            return false;
        }

        return $dateStart <= $now && $now <= $dateEnd;
    }
}
