<?php

namespace aton\tools\Atp;

use aton\tools\Atp\AtpException\AddApplicationAtpException;
use aton\tools\Atp\AtpException\DuplicateAppAtpException;
use aton\tools\Atp\AtpException\NotFoundAppAtpException;
use aton\tools\Atp\AtpException\NotFoundStatusAtpException;
use aton\tools\Atp\AtpException\NotFoundStatusDbAtpException;
use aton\tools\Atp\AtpException\UpdateApplicationAtpException;
use aton\tools\Atp\Status\AbstractStatus;
use aton\tools\Atp\Status\OpenDoors\StatusDoorsInvited;
use aton\tools\Atp\Status\OpenDoors\StatusDoorsNew;
use aton\tools\Atp\Status\OpenDoors\StatusDoorsNewRejected;
use aton\tools\Atp\Status\OpenDoors\StatusDoorsTestFinished;
use aton\tools\Atp\Status\StatusCompleted;
use aton\tools\Atp\Status\StatusInterviewRejected;
use aton\tools\Atp\Status\StatusInvited;
use aton\tools\Atp\Status\StatusInvitedVcv;
use aton\tools\Atp\Status\StatusItInterviewRejected;
use aton\tools\Atp\Status\StatusItNew;
use aton\tools\Atp\Status\StatusItNewRejected;
use aton\tools\Atp\Status\StatusItUserRejected;
use aton\tools\Atp\Status\StatusNew;
use aton\tools\Atp\Status\StatusNewRejected;
use aton\tools\Atp\Status\StatusTaskFinished;
use aton\tools\Atp\Status\StatusTaskRejected;
use aton\tools\Atp\Status\StatusTaskSent;
use aton\tools\Atp\Status\StatusTaskSigned;
use aton\tools\Atp\Status\StatusTaskUnderReview;
use aton\tools\Atp\Status\StatusTestFinished;
use aton\tools\Atp\Status\StatusTestRejected;
use aton\tools\Atp\Status\StatusUserRejected;
use aton\tools\Tables\AtpApplicationFilesTable;
use aton\tools\Tables\AtpApplicationTable;
use aton\tools\Tables\AtpEventTable;
use aton\tools\Tables\AtpStatusTable;
use aton\tools\Utils\UserError;
use \aton\Main\Application;
use aton\Main\Entity\ExpressionField;
use \aton\Main\Localization\Loc;
use \aton\Main\UserConsent\Agreement;
use \aton\Main\UserConsent\Consent;
use \aton\Main\Type\DateTime;

class AtpApplication
{
    private $data = [];
    /**
     * @var UserError
     */
    private $userError = null;

    /**
     * @var AbstractStatus
     */
    private $status = null;

    /**
     * @var AbstractStatus
     */
    private $previousStatus = null;

    /**
     * @var string
     */
    private $eventType = '';

    /**
     * @var array
     */
    private $selectedFiles = [];

    /**
     * Files were sent by HR
     * @var array
     */
    private $atpFiles = null;

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
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return String
     */
    public function getStatusCode(): String
    {
        return $this->status->getStatusCode();
    }

    /**
     * @return String|null
     */
    public function getStatusCodeTestFinished(): ?String
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
    public function getStatusTest(): String
    {
        return $this->status->getStatusCode();
    }

    /**
     * Name for email
     * @return String
     */
    public function getOnlyName(): String
    {
        $data = $this->getData();
        $name = preg_replace('/ {2,}/', ' ', trim($data['NAME']));
        $arName = explode(' ' , $name);
        return $arName[1];
    }

    /**
     * @return String
     */
    public function getEmail(): String
    {
        $data = $this->getData();
        return $data['EMAIL'];
    }

    /**
     * @return String|null
     */
    public function getYear(): String
    {
        $data = $this->getData();
        return $data['EVENT_YEAR'];
    }

    /**
     * @return String
     */
    public function getAtpFileId(): String
    {
        $data = $this->getData();
        return $data['APP_FILE_ID'];
    }

    /**
     * @return String
     */
    public function getComment(): String
    {
        $data = $this->getData();
        return $data['COMMENT'];
    }

    /**
     * @return array
     */
    public function getSelectedFiles(): array
    {
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
     * Get files from HR
     * @return array
     */
    public function getAtpFiles(): array
    {
        if($this->atpFiles == null) {
            $this->atpFiles = $this->loadAtpFiles();
        }
        return $this->atpFiles;
    }


    /**
     * application constructor
     * @param $data
     */
    public function __construct($data)
    {
        $this->userError = new UserError();
        $this->data = $data;
        $this->defineEventType();
        $this->defineStatus($this->data['STATUS_CODE']);
    }

    /**
     * @return bool
     */
    public function checkErrors(): bool
    {
        return $this->userError->checkErrors();
    }

    /**
     * save with checking
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function save() {
        $this->updateStatus();
        if(!$this->getApplicationId()) { //Если новая заявка
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
    }

    /**
     * @throws DuplicateAppAtpException
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function checkDuplicate() {
        if($this->eventType != 'ITTP') { //ittp allowed
            $result = AtpApplicationTable::getList([
                'filter' => [
                    'USER_ID' => $this->data['USER_ID'],
                    'EVENT_ID' => $this->data['EVENT_ID'],
                    //'RESULT_ID' => $this->data['RESULT_ID'],
                ],
            ])->fetch();
            if ($result) {
                $this->duplicatedData = $result;
                throw new DuplicateAppAtpException($this->data);
            }
        }
    }

    /**
     * load atp files from trainee
     * @return array
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function loadAtpFiles() {
        $atpFiles = [];
        $rsData = AtpApplicationFilesTable::getList([
            'select' => ['*', 'FILE_' => 'ATP_FILE'],
            'filter' => ['APPLICATION_ID' => $this->getApplicationId()],
        ]);
        while ($row = $rsData->fetch())
        {
            $atpFiles[$row['ATP_FILE_ID']] = $row;
        }
        return $atpFiles;
    }

    /**
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    private function defineEventType() {
        if($this->data['EVENT_TYPE']) { //Если заявку взяли из БД
            $this->eventType = $this->data['EVENT_TYPE'];
        } elseif($this->data['EVENT_ID']) { //Если это новая заявка
            $event = AtpEventTable::getList([
                'filter' => ['ID' => $this->data['EVENT_ID']],
                'cache' => array(
                    'ttl' => 60
                )
            ])->fetch();
            $this->eventType = $event['TYPE'];
            $this->data['EVENT_YEAR'] = $event['YEAR'];
        }
    }

    /**
     * save without checking
     * @throws AddApplicationAtpException
     */
    private function add() {
        $result = AtpApplicationTable::add($this->data);
        if (!$result->isSuccess())
        {
            throw new AddApplicationAtpException($this->data, $result->getErrorMessages());
        }
    }

    private function update() {
        $result = AtpApplicationTable::update($this->getApplicationId(), $this->data);
        if (!$result->isSuccess())
        {
            throw new UpdateApplicationAtpException($this->data, $result->getErrorMessages());
        }
    }

    private function updateStatus() {
        $newStatusCode = $this->status->getStatusCode();
        if($this->data['STATUS_CODE'] != $newStatusCode) {
            $status = AtpStatusTable::getList([
                'filter' => ['CODE' => $newStatusCode],
            ])->fetch();
            if($status) {
                $this->data['STATUS_ID'] = $status['ID'];
                $this->data['STATUS_CODE'] = $status['CODE'];
                $this->data['STATUS_VALUE'] = $status['VALUE'];
            } else {
                throw new NotFoundStatusDbAtpException($newStatusCode);
            }
        }
    }

    /**
     * Get application instance by application id
     * @param $id
     * @return static
     * @throws NotFoundAppAtpException
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public static function getById($id) {
        $result = AtpApplicationTable::getList([
            'select' => [
                '*',
                'EVENT_YEAR' => 'EVENT.YEAR',
                'EVENT_TYPE' => 'EVENT.TYPE',
                'USER_NAME' => 'USER.NAME',
                'USER_LAST_NAME' => 'USER.LAST_NAME',
                'USER_SECOND_NAME' => 'USER.SECOND_NAME',
                'USER_EMAIL' => 'USER.EMAIL',
                'STATUS_CODE' => 'STATUS.CODE',
                'STATUS_VALUE' => 'STATUS.VALUE',
                'APP_FILE_NAME' => 'APP_FILE.UF_NAME',
            ],
            'filter' => [
                'ID' => $id,
            ],
        ])->fetch();
        if($result) {
            $application = new static($result);
        } else {
            throw new NotFoundAppAtpException($id);
        }
        return $application;
    }

    /**
     * Get application instance  by User and event type
     * @param int $eventId
     * @param int $userId
     * @param int|null $atpFileId
     * @return static|null
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public static function getByUser(int $eventId, int $userId, int $appFileId = null) {
        $filter = [
            'EVENT_ID' => $eventId,
            'USER_ID' => $userId,
        ];
        if($appFileId) {
            $filter['APP_FILE_ID'] = $appFileId;
        }
        $result = AtpApplicationTable::getList([
            'select' => [
                '*',
                'EVENT_YEAR' => 'EVENT.YEAR',
                'EVENT_TYPE' => 'EVENT.TYPE',
                'COURSE_ID' => 'EVENT.COURSE_ID',
                'TEST_ID' => 'EVENT.TEST_ID',
                'USER_NAME' => 'USER.NAME',
                'USER_LAST_NAME' => 'USER.LAST_NAME',
                'USER_SECOND_NAME' => 'USER.SECOND_NAME',
                'USER_EMAIL' => 'USER.EMAIL',
                'STATUS_CODE' => 'STATUS.CODE',
                'STATUS_VALUE' => 'STATUS.VALUE',
                'APP_FILE_NAME' => 'APP_FILE.UF_NAME',
            ],
            'filter' => $filter,
        ])->fetch();
        if($result) {
            //$result['FULL_NAME'] = AtpApplicationController::getFullName($result);
            $application = new static($result);
        } else {
            $application = null;
        }
        return $application;
    }

    /**
     * Wrapper for the getByUser method with determination of the current event id
     * @param String $eventCode
     * @param int|null $atpFileId
     * @return AtpApplication|null
     * @throws NotFoundAppAtpException
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public static function getByUserEvent(String $eventCode = 'ATP', int $appFileId = null): ?AtpApplication
    {
        global $USER;
        $eventController = new AtpEventController();
        $events = $eventController->getEvents();
        $atpEvent = $events[$eventCode];
        return $USER->IsAuthorized() ? self::getByUser($atpEvent['ID'], $USER->GetID(), $appFileId) : null;
    }

    /**
     * @param String $statusCode
     * @throws NotFoundStatusAtpException
     */
    public function changeStatusByCode(String $statusCode) {
        $this->defineStatus($statusCode);
    }

    /**
     * set testid result
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
     * @param int $resultId
     */
    public function changeTaskResult(int $resultId): void
    {
        $this->data['TEST_FILE_ID'] = $resultId;
    }

    /**
     * @param String $comment
     */
    public function addUserComment(String $comment): void
    {
        $this->data['USER_COMMENT'] = $this->data['USER_COMMENT'] ? $this->data['USER_COMMENT'] . '\n' . $comment : $comment;
    }

    /**
     * define status instance by status code
     * @param $statusCode
     */
    private function defineStatus($statusCode) {
        $this->previousStatus = $this->status;
        if($statusCode) {
            switch($statusCode) {
                //ATP
                case 'NEW':
                    $this->status = new StatusNew(); break;
                case "NEW_REJECTED":
                    $this->status = new StatusNewRejected(); break;
                case "TEST_FINISHED":
                    $this->status = new StatusTestFinished(); break;
                case "TEST_REJECTED":
                    $this->status = new StatusTestRejected(); break;
                case "INVITED_VCV":
                    $this->status = new StatusInvitedVcv(); break;
                case "INTERVIEW_REJECTED":
                    $this->status = new StatusInterviewRejected(); break;
                case "USER_REJECTED":
                    $this->status = new StatusUserRejected(); break;
                //ITTP
                case 'IT_NEW':
                    $this->status = new StatusItNew(); break;
                case "IT_NEW_REJECTED":
                    $this->status = new StatusItNewRejected(); break;
                case "TASK_SENT":
                    $this->status = new StatusTaskSent(); break;
                case "TASK_FINISHED":
                    $this->status = new StatusTaskFinished(); break;
                case "TASK_UNDER_REVIEW":
                    $this->status = new StatusTaskUnderReview(); break;
                case "TASK_REJECTED":
                    $this->status = new StatusTaskRejected(); break;
                case "INVITED":
                    $this->status = new StatusInvited(); break;
                case "COMPLETED":
                    $this->status = new StatusCompleted(); break;
                case "IT_INTERVIEW_REJECTED":
                    $this->status = new StatusItInterviewRejected(); break;
                case "IT_USER_REJECTED":
                    $this->status = new StatusItUserRejected(); break;
                //Open Doors
                case "DOORS_NEW":
                    $this->status = new StatusDoorsNew(); break;
                case "DOORS_NEW_REJECTED":
                    $this->status = new StatusDoorsNewRejected(); break;
                case "DOORS_INVITED":
                    $this->status = new StatusDoorsInvited(); break;
                case "DOORS_TEST_FINISHED":
                    $this->status = new StatusDoorsTestFinished(); break;
            }
            if(!$this->status)
                throw new NotFoundStatusAtpException($statusCode);
        } else {
            if($this->getEventType() == 'ITTP') {
                $this->status = new StatusItNew();
            } elseif($this->getEventType() == 'OPEN_DOORS') {
                $this->status = new StatusDoorsNew();
            } else {
                $this->status = new StatusNew();
            }
        }
        $this->status->setApplication($this);
    }

    /**
     * get button to next status for admin panel
     * @return mixed|string
     */
    public function getNextButton() {
        return $this->status->getNextButton();
    }

    /**
     * get button to next cancel for admin panel
     * @return mixed|string
     */
    public function getRejectButton() {
        return $this->status->getRejectButton();
    }

    /**
     * Send email by status instance
     */
    public function sendEmail() {
        $this->status->sendEmail();
    }

    public function isRejectedStatus() {
        return $this->status->isRejectedStatus();
    }

}
