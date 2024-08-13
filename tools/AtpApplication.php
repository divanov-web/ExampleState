<?php
/**
 * Класс для одного экземлпляра заявки стажёра
 */

namespace aton\tools\Atp;

use aton\tools\Atp\AtpException\AddApplicationAtpException;
use aton\tools\Atp\AtpException\DuplicateAppAtpException;
use aton\tools\Atp\AtpException\NotFoundAppAtpException;
use aton\tools\Atp\AtpException\NotFoundStatusAtpException;
use aton\tools\Atp\AtpException\NotFoundStatusDbAtpException;
use aton\tools\Atp\AtpException\UpdateApplicationAtpException;
use aton\tools\Atp\Department\AtpAppDepartments;
use aton\tools\Atp\Status\AbstractStatus;
use aton\tools\Atp\Status\Aod\StatusAodCompleted;
use aton\tools\Atp\Status\Atp\StatusAtpBossInterview;
use aton\tools\Atp\Status\Atp\StatusAtpBossInterviewReject;
use aton\tools\Atp\Status\Atp\StatusAtpClasses;
use aton\tools\Atp\Status\Atp\StatusAtpClassesRejected;
use aton\tools\Atp\Status\Atp\StatusAtpDepartments;
use aton\tools\Atp\Status\Atp\StatusAtpDocuments;
use aton\tools\Atp\Status\Atp\StatusAtpOffer;
use aton\tools\Atp\Status\Atp\StatusAtpOfferReject;
use aton\tools\Atp\Status\Atp\StatusAtpResult;
use aton\tools\Atp\Status\Atp\StatusInterviewRejected;
use aton\tools\Atp\Status\Atp\StatusInvitedVcv;
use aton\tools\Atp\Status\Atp\StatusNew;
use aton\tools\Atp\Status\Atp\StatusNewRejected;
use aton\tools\Atp\Status\Atp\StatusTestFinished;
use aton\tools\Atp\Status\Atp\StatusTestRejected;
use aton\tools\Atp\Status\Atp\StatusUserRejected;
use aton\tools\Atp\Status\Aod\StatusAodInvited;
use aton\tools\Atp\Status\Aod\StatusAodNew;
use aton\tools\Atp\Status\Aod\StatusAodNewRejected;
use aton\tools\Atp\Status\Aod\StatusAodTestFinished;
use aton\tools\Atp\Status\StatusCompleted;
use aton\tools\Atp\Status\StatusInvited;
use aton\tools\Atp\Status\StatusItInterviewRejected;
use aton\tools\Atp\Status\StatusItNew;
use aton\tools\Atp\Status\StatusItNewRejected;
use aton\tools\Atp\Status\StatusItUserRejected;
use aton\tools\Atp\Status\StatusTaskFinished;
use aton\tools\Atp\Status\StatusTaskRejected;
use aton\tools\Atp\Status\StatusTaskSent;
use aton\tools\Atp\Status\StatusTaskUnderReview;
use aton\tools\HLTables\ListAtpFilesTable;
use aton\tools\Tables\Atp\AtpApplicationFilesTable;
use aton\tools\Tables\Atp\AtpApplicationTable;
use aton\tools\Tables\Atp\AtpEventTable;
use aton\tools\Tables\Atp\AtpStatusTable;
use aton\tools\Utils\UserError;
use aton\Main\Type\DateTime;

class AtpApplication
{
    private $id = null;
    private $data = [];
    /**
     * @var UserError
     */
    private $userError = null;

    /**
     * @var AbstractStatus Ссылка на текущее состояние
     */
    private $status = null;

    /**
     * @var AbstractStatus Ссылка на предыдущее состояние
     */
    private $previousStatus = null;

    /**
     * Тип заявки IT или MAIN
     * @var string
     */
    private $eventType = '';

    /**
     * @var array
     */
    private $selectedFiles = [];

    /**
     * Список отправленных файлов ТЗ. Получены из БД при выборке по id заявки
     * @var array
     */
    private $atpFiles = null;

    private ?AtpAppDepartments $departments = null;

    private ?AtpAppFiles $files = null;

    /**
     * @return UserError
     */
    public function getUserError(): UserError
    {
        return $this->userError;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @return int
     */
    public function getApplicationId(): int
    {
        return (int)$this->data['ID'];
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return (int)$this->data['USER_ID'];
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id ?: (int)$this->data['ID'];
    }

    /**
     * Получить заявку в виде массива
     * @return array
     */
    public function getData(): array
    {
        $arData = $this->data;
        if ($this->departments) {
            $arData['DEPARTMENTS'] = $this->getFirstStatusDepartments();
            $arData['DEPARTMENTS_CLASSES'] = $this->getClassesStatusDepartments();
        }
        if ($this->files) {
            $arData['FILES'] = $this->getFiles();
        }
        return $arData;
    }

    /**
     * @return String
     */
    public function getStatusCode(): string
    {
        return $this->status->getStatusCode();
    }

    /**
     * Получить текст статуса для отображения в карточке
     * @return String
     */
    public function getStatusTagValue(): string
    {
        return (string)$this->data['STATUS_TAG_VALUE'];
    }

    /**
     * @return String|null
     */
    public function getStatusCodeTestFinished(): ?string
    {
        return $this->status->getStatusCodeTestFinished();
    }

    /**
     * @return AbstractStatus
     */
    public function getPreviousStatus(): ?AbstractStatus
    {
        return $this->previousStatus;
    }

    /**
     * @return String
     */
    public function getStatusTest(): string
    {
        return $this->status->getStatusCode();
    }

    /**
     * Получить массив привязанных выбранных подразделений первого шага
     * @param bool $onlyNames
     * @return array|null
     */
    public function getFirstStatusDepartments(bool $onlyNames = false)
    {
        return $this->departments->getFirstStatusDepartments($onlyNames);
    }

    /**
     * Получить массив привязанных выбранных подразделений на стадии учебных классов
     * @param bool $onlyNames
     * @return array|null
     */
    public function getClassesStatusDepartments(bool $onlyNames = false)
    {
        return $this->departments->getClassesStatusDepartments($onlyNames);
    }

    /**
     * Получить массив с файлами
     * @return array|null
     */
    public function getFiles()
    {
        return $this->files->getFiles();
    }

    /**
     * Получить массив с удалёнными файлами
     * @return array|null
     */
    public function getDeletedFiles()
    {
        return $this->files->getDeletedFiles();
    }

    /**
     * @return String
     */
    public function getName(): string
    {
        $data = $this->getData();
        return $data['NAME'];
    }

    /**
     * Фамилия и имя
     * @return string
     */
    public function getFullName(): string
    {
        $data = $this->getData();
        return $data['NAME'] . ' ' . $data['LAST_NAME'];
    }

    /**
     * @return String
     */
    public function getEmail(): string
    {
        $data = $this->getData();
        return $data['EMAIL'];
    }

    /**
     * @return String|null
     */
    public function getYear(): string
    {
        $data = $this->getData();
        return $data['EVENT_YEAR'];
    }

    /**
     * @return String
     */
    /*public function getAtpFileId(): String
    {
        $data = $this->getData();
        return $data['APP_FILE_ID'];
    }*/

    /**
     * @return String
     */
    public function getComment(): string
    {
        $data = $this->getData();
        return $data['COMMENT'];
    }

    /**
     * Получить id файла оффера
     * @return int
     */
    public function getOfferFileId(): int
    {
        $data = $this->getData();
        return $data['OFFER_FILE_ID'];
    }

    /**
     * Получить первый рабочий день
     * @return DateTime
     */
    public function getFirstDay(): DateTime
    {
        $data = $this->getData();
        return $data['FIRST_DAY'];
    }

    /**
     * Получить id файлов, котоыре отправили hr по ittp
     * @return array
     */
    public function getSelectedFiles(): array
    {
        if (!$this->selectedFiles) {
            $rsDataFile = AtpApplicationFilesTable::getList([
                'select' => ['*'],
                'filter' => ['APPLICATION_ID' => $this->getId()],
            ]);
            while ($rowFile = $rsDataFile->fetch()) {
                $this->selectedFiles[] = $rowFile['ATP_FILE_ID'];
            }
        }
        return $this->selectedFiles;
    }

    /**
     * @param array $selectedFiles
     */
    public function setSelectedFiles(array $selectedFiles): void
    {
        $this->selectedFiles = $selectedFiles;
    }

    /**
     * Добавить id файла оффера (или сертификата для AOD) в заявку
     * @param int $fileId
     * @return void
     */
    public function setOfferFile(int $fileId): void
    {
        $this->data['OFFER_FILE_ID'] = $fileId;
    }

    /**
     * Устанавливает дату первого рабочего дня
     * @param DateTime $dateTime
     * @return void
     */
    public function setFirstDate(DateTime $dateTime): void
    {
        $this->data['FIRST_DAY'] = $dateTime;
    }

    /**
     * Возвращает файлы ТЗ, которые были отправлены кадрами
     * @return array
     */
    public function getAtpFiles(): array
    {
        if ($this->atpFiles == null) {
            $this->atpFiles = $this->loadAtpFiles();
        }
        return $this->atpFiles;
    }


    /**
     * Формирует объект заявки стажёра из массива данных
     * @param $data
     * @param bool $needDetail - делать запросы на дополнительные данные (выбраныне департаменты, файлы)
     */
    public function __construct($data, bool $needDetail = false)
    {
        $this->userError = new UserError();
        $this->data = $data;
        $this->defineEventType();
        $this->defineStatus($this->data['STATUS_CODE']);
        $this->departments = new AtpAppDepartments($this);
        if ($needDetail) {
            $this->files = new AtpAppFiles($this);
        }
    }

    /**
     * @return bool
     */
    public function checkErrors(): bool
    {
        return $this->userError->checkErrors();
    }

    /**
     * Сохранение с проверкой
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function save()
    {
        $this->updateStatus();
        if (!$this->getApplicationId()) { //Если новая заявка
            try {
                $this->checkDuplicate();
                $this->add();
            } catch (DuplicateAppAtpException $exception) {
                $this->userError->addError('Вы уже проходили регистрацию на стажировку в этом году');
            } catch (AddApplicationAtpException $exception) {
                $this->userError->addError('Не получилось сохранить регистрацию на стажировку');
            }
        } else {
            $this->update();
        }
        if (!empty($this->selectedFiles)) { //Если были добавлены файлы ТЗ
            $this->saveApplicationFiles();
        }
    }

    /**
     * Сохраняет выбранные файлы ТЗ для отправки стажёрам
     */
    public function saveApplicationFiles()
    {
        $oldAtpFiles = $this->loadAtpFiles();
        $oldAtpFilesIds = array_keys($oldAtpFiles);

        foreach ($this->selectedFiles as $atpFileId) {
            if (!in_array($atpFileId, $oldAtpFilesIds)) {
                $fileRow = ['APPLICATION_ID' => $this->getApplicationId(), 'ATP_FILE_ID' => $atpFileId];
                AtpApplicationFilesTable::add($fileRow);
            }
            unset($oldAtpFiles[$atpFileId]);
        }

        if (!empty($oldAtpFiles)) {
            foreach ($oldAtpFiles as $oldFile) {
                AtpApplicationFilesTable::delete($oldFile['ID']);
            }
        }
    }

    /**
     * Проверка, что кандидат уже отправлял заявку на эту стажировку
     * @throws DuplicateAppAtpException
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function checkDuplicate()
    {
        if ($this->eventType != 'ITTP') { //дубликаты на ittp разрешены
            $result = AtpApplicationTable::getList([
                'filter' => [
                    'USER_ID' => $this->data['USER_ID'],
                    'EVENT_ID' => $this->data['EVENT_ID'],
                    //'RESULT_ID' => $this->data['RESULT_ID'],
                ],
            ])->fetch();
            if ($result) {
                //$this->duplicatedData = $result;
                throw new DuplicateAppAtpException($this->data);
            }
        }
    }

    /**
     * Получить список отправленных ТЗ для этой заявки
     * @return array
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function loadAtpFiles()
    {
        $ittpTasks = [];
        //Список всех возможных заданий из HL-блока со списком доступных заданий для получения названия
        $rsDataAtpFile = ListAtpFilesTable::getList([
            'select' => ['*'],
        ]);
        while ($arItemFile = $rsDataAtpFile->fetch()) {
            $ittpTasks[$arItemFile['ID']] = $arItemFile;
        }

        //Список заданий, которые отправили кадры
        $atpFiles = [];
        $rsData = AtpApplicationFilesTable::getList([
            'select' => ['*'], //'FILE_' => 'ATP_FILE'
            'filter' => ['APPLICATION_ID' => $this->getApplicationId()],
        ]);
        while ($row = $rsData->fetch()) {
            $ittpTask = $ittpTasks[$row['ATP_FILE_ID']];
            $row['FILE_NAME'] = $ittpTask['UF_NAME'];
            $atpFiles[$row['ATP_FILE_ID']] = $row;
        }
        return $atpFiles;
    }

    /**
     * Установка свойства объекта eventType из БД или из массива, в зависимости от способа создания объекта
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    private function defineEventType()
    {
        if ($this->data['EVENT_TYPE']) { //Если заявку взяли из БД
            $this->eventType = $this->data['EVENT_TYPE'];
        } elseif ($this->data['EVENT_ID']) { //Если это новая заявка
            $event = AtpEventTable::getList([
                'filter' => ['ID' => $this->data['EVENT_ID']],
                'cache' => [
                    'ttl' => 60
                ]
            ])->fetch();
            $this->eventType = $event['TYPE'];
            $this->data['EVENT_YEAR'] = $event['YEAR'];
        }
    }

    /**
     * Сохранение без проверки. Частный метод.
     * @throws AddApplicationAtpException
     */
    private function add()
    {
        $result = AtpApplicationTable::add($this->data);
        if (!$result->isSuccess()) {
            throw new AddApplicationAtpException($this->data, $result->getErrorMessages());
        }
        $this->id = $result->getId();
    }

    private function update()
    {
        $result = AtpApplicationTable::update($this->getApplicationId(), $this->data);
        if (!$result->isSuccess()) {
            throw new UpdateApplicationAtpException($this->data, $result->getErrorMessages());
        }
    }

    private function updateStatus()
    {
        $newStatusCode = $this->status->getStatusCode();
        if ($this->data['STATUS_CODE'] != $newStatusCode) {
            $status = AtpStatusTable::getList([
                'filter' => ['CODE' => $newStatusCode],
                'cache' => [
                    'ttl' => 60
                ]
            ])->fetch();
            if ($status) {
                $this->data['STATUS_ID'] = $status['ID'];
                $this->data['STATUS_CODE'] = $status['CODE'];
                $this->data['STATUS_VALUE'] = $status['VALUE'];
            } else {
                throw new NotFoundStatusDbAtpException($newStatusCode);
            }
        }
    }

    /**
     * Статический метод, создающий объект заявки из БД по ID
     * Возвращает экземпляр класса
     * @param $id
     * @return static
     * @throws NotFoundAppAtpException
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public static function getById($id)
    {
        $result = AtpApplicationTable::getList([
            'select' => [
                '*',
                'EVENT_YEAR' => 'EVENT.YEAR',
                'EVENT_TYPE' => 'EVENT.TYPE',
                'STATUS_CODE' => 'STATUS.CODE',
                'STATUS_VALUE' => 'STATUS.VALUE',
                'STAGE_ID' => 'STATUS.STAGE_ID',
                'STATUS_TAG_VALUE' => 'STATUS.TAG_VALUE',
                'STAGE_DATE_START' => 'STATUS.STAGE.DATE_START',
                'STAGE_DATE_END' => 'STATUS.STAGE.DATE_END',
            ],
            'filter' => [
                'ID' => $id,
            ],
        ])->fetch();
        if ($result) {
            $application = new static($result, true);
        } else {
            throw new NotFoundAppAtpException($id);
        }
        return $application;
    }

    /**
     * Статический метод, создающий объект заявки из БД по пользователю и событию
     * Возвращает экземпляр класса
     * @param int $eventId
     * @param int $userId
     * @param int|null $appFileId
     * @return static|null
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public static function getByUser(int $eventId, int $userId)
    {
        $filter = [
            'EVENT_ID' => $eventId,
            'USER_ID' => $userId,
        ];
        $result = AtpApplicationTable::getList([
            'select' => [
                '*',
                'EVENT_YEAR' => 'EVENT.YEAR',
                'EVENT_TYPE' => 'EVENT.TYPE',
                'COURSE_ID' => 'EVENT.COURSE_ID',
                'TEST_ID' => 'EVENT.TEST_ID',
                'STATUS_CODE' => 'STATUS.CODE',
                'STATUS_VALUE' => 'STATUS.VALUE',
                'STAGE_ID' => 'STATUS.STAGE_ID',
                'STATUS_TAG_VALUE' => 'STATUS.TAG_VALUE',
                'STAGE_DATE_START' => 'STATUS.STAGE.DATE_START',
                'STAGE_DATE_END' => 'STATUS.STAGE.DATE_END',
            ],
            'filter' => $filter,
        ])->fetch();
        if ($result) {
            $application = new static($result, true);
        } else {
            $application = null;
        }
        return $application;
    }

    /**
     * Обёртка для метода getByUser с самостоятельным определением текущего id события atp(ittp) и userId
     * @param String $eventCode
     * @param int|null $atpFileId
     * @return AtpApplication|null
     * @throws NotFoundAppAtpException
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public static function getByUserEvent(string $eventCode = 'ATP'): ?AtpApplication
    {
        global $USER;
        $eventController = new AtpEventController();
        $events = $eventController->getEvents();
        $atpEvent = $events[$eventCode];
        return $USER->IsAuthorized() ? self::getByUser($atpEvent['ID'], $USER->GetID()) : null;
    }

    /**
     * @param String $statusCode
     * @throws NotFoundStatusAtpException
     */
    public function changeStatusByCode(string $statusCode)
    {
        $this->defineStatus($statusCode);
    }

    /**
     * Установка id прохождения теста. После требуется сохранение.
     * @param int $attemptId
     */
    public function changeAttempt(int $attemptId): void
    {
        $this->data['ATTEMPT_ID'] = $attemptId;
    }

    /**
     * @param string $comment
     */
    public function changeComment(string $comment): void
    {
        $this->data['COMMENT'] = $comment;
    }

    /**
     * @param string $email
     */
    public function changeEmail(string $email): void
    {
        $this->data['EMAIL'] = $email;
    }

    /**
     * ID результата с формой, где лежат файлы ответов на ТЗ
     * @param int $resultId
     */
    public function changeTaskResult(int $resultId): void
    {
        $this->data['TEST_FILE_ID'] = $resultId;
    }

    /**
     * Добавление комментария к заявке при отказе стажёра от участия
     * @param String $comment
     */
    public function addUserComment(string $comment): void
    {
        $this->data['USER_COMMENT'] = $this->data['USER_COMMENT'] ? $this->data['USER_COMMENT'] . '\n' . $comment : $comment;
    }

    /**
     * Создаёт статус заявки в виде объекта статусов по коду статуса
     * @param $statusCode
     */
    private function defineStatus($statusCode)
    {
        $this->previousStatus = $this->status;
        if ($statusCode) {
            $this->status = self::getStatusByCode($statusCode);
            if (!$this->status)
                throw new NotFoundStatusAtpException($statusCode);
        } else {
            if ($this->getEventType() == 'ITTP') {
                $this->status = new StatusItNew();
            } elseif ($this->getEventType() == 'OPEN_DOORS') {
                $this->status = new StatusAodNew();
            } else {
                $this->status = new StatusNew();
            }
        }
        $this->status->setApplication($this);
    }

    /**
     * Получить объект статуса по его коду
     * @param string $statusCode
     * @return AbstractStatus|null
     */
    static public function getStatusByCode(string $statusCode): ?AbstractStatus
    {
        $status = null;
        switch ($statusCode) {
            //ATP
            case StatusNew::$statusCode:
                $status = new StatusNew();
                break;
            case StatusNewRejected::$statusCode:
                $status = new StatusNewRejected();
                break;
            case StatusTestFinished::$statusCode:
                $status = new StatusTestFinished();
                break;
            case StatusTestRejected::$statusCode:
                $status = new StatusTestRejected();
                break;
            case StatusInvitedVcv::$statusCode:
                $status = new StatusInvitedVcv();
                break;
            case StatusInterviewRejected::$statusCode:
                $status = new StatusInterviewRejected();
                break;
            case StatusUserRejected::$statusCode:
                $status = new StatusUserRejected();
                break;
            case StatusAtpClasses::$statusCode:
                $status = new StatusAtpClasses();
                break;
            case StatusAtpDepartments::$statusCode:
                $status = new StatusAtpDepartments();
                break;
            case StatusAtpClassesRejected::$statusCode:
                $status = new StatusAtpClassesRejected();
                break;
            case StatusAtpBossInterview::$statusCode:
                $status = new StatusAtpBossInterview();
                break;
            case StatusAtpBossInterviewReject::$statusCode:
                $status = new StatusAtpBossInterviewReject();
                break;
            case StatusAtpDocuments::$statusCode:
                $status = new StatusAtpDocuments();
                break;
            case StatusAtpOffer::$statusCode:
                $status = new StatusAtpOffer();
                break;
            case StatusAtpOfferReject::$statusCode:
                $status = new StatusAtpOfferReject();
                break;
            case StatusAtpResult::$statusCode:
                $status = new StatusAtpResult();
                break;
            //ITTP
            case StatusItNew::$statusCode:
                $status = new StatusItNew();
                break;
            case StatusItNewRejected::$statusCode:
                $status = new StatusItNewRejected();
                break;
            case StatusTaskSent::$statusCode:
                $status = new StatusTaskSent();
                break;
            case StatusTaskFinished::$statusCode:
                $status = new StatusTaskFinished();
                break;
            case StatusTaskUnderReview::$statusCode:
                $status = new StatusTaskUnderReview();
                break;
            case StatusTaskRejected::$statusCode:
                $status = new StatusTaskRejected();
                break;
            case StatusInvited::$statusCode:
                $status = new StatusInvited();
                break;
            case StatusCompleted::$statusCode:
                $status = new StatusCompleted();
                break;
            case StatusItInterviewRejected::$statusCode:
                $status = new StatusItInterviewRejected();
                break;
            case StatusItUserRejected::$statusCode:
                $status = new StatusItUserRejected();
                break;
            //Open Doors
            case StatusAodNew::$statusCode:
                $status = new StatusAodNew();
                break;
            case StatusAodNewRejected::$statusCode:
                $status = new StatusAodNewRejected();
                break;
            case StatusAodInvited::$statusCode:
                $status = new StatusAodInvited();
                break;
            case StatusAodTestFinished::$statusCode:
                $status = new StatusAodTestFinished();
                break;
            case StatusAodCompleted::$statusCode:
                $status = new StatusAodCompleted();
                break;
        }

        return $status;
    }

    /**
     * Выводит кнопку для перехода в следующий статус
     * @return mixed|string
     */
    public function getNextButton()
    {
        return $this->status->getNextButton();
    }

    /**
     * Выводит кнопку для перехода в статус отклонения
     * @return mixed|string
     */
    public function getRejectButton()
    {
        return $this->status->getRejectButton();
    }

    /**
     * Делегирование отправки письма статусу анкеты
     * Отправляет письмо текущего статуса, учитывая предыдущий статус
     */
    public function sendEmail()
    {
        $this->status->sendEmail();
    }

    /**
     * Является ли статус типом отклонённых
     */
    public function isRejectedStatus()
    {
        return $this->status->isRejectedStatus();
    }

    /**
     * Получить кнопку текущего статуса для кабинета стажёра
     * @return array|null
     */
    public function getUserButton()
    {
        return $this->status->getUserButton();
    }

    /**
     * Проверяет являются ли даты текущего статуса активными на данный момент
     * @return bool
     */
    public function isStatusActiveDate(): bool
    {
        return $this->status->isActiveDate();
    }
}
