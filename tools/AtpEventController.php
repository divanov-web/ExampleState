<?php

namespace aton\tools\Atp;

use aton\tools\Atp\AtpException\AddEventAtpException;
use aton\tools\Tables\AtpApplicationTable;
use aton\tools\Tables\AtpEventTable;
use \aton\Main\Application;
use \aton\Main\Localization\Loc;
use \aton\Main\UserConsent\Agreement;
use \aton\Main\UserConsent\Consent;
use \aton\Main\Type\DateTime;

class AtpEventController
{
    private $year = null;
    private $eventValues = [];
    private $events = [];

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Возвращает стажировку за этот год по коду стажировки
     * @param String $type
     * @return mixed
     */
    public function getEventByType(String $type) {
        $events = $this->getEvents();
        return $events[$type];
    }

    public function __construct()
    {
        $this->year = date('Y');
        $this->events = $this->getActualEvents();
        if(empty($this->events) || count($this->events) != count($this->eventValues) ) {
            $this->createActualEvents();
            $this->events = $this->getActualEvents();
        }
    }

    /**
     * Проверка существования действующих мероприятий и их создание
     * @return array
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function getActualEvents() {
        $this->eventValues = AtpEventTable::getEntity()->getField('TYPE')->getValues();
        $arEvents = [];
        $result = AtpEventTable::getList([
            'select' => ['*'],
            'filter' => [
                'YEAR' => $this->year,
                'TYPE' => $this->eventValues,
            ],
        ]);
        while ($row = $result->fetch())
        {
            $row['URL_CODE'] = strtolower(str_replace("_", "", $row['TYPE'])); //код события для url
            $row['URL_REGISTRATION'] = "/start/".$row['URL_CODE']."/form/";
            $arEvents[$row['TYPE']] = $row;
        }
        return $arEvents;
    }

    /**
     * Создать мероприятия ATP для текущего года
     * @throws AddEventAtpException
     */
    public function createActualEvents() {
        foreach($this->eventValues as $eventType) {
            if(!$this->events[$eventType]) {
                $result = AtpEventTable::add([
                    'YEAR' => $this->year,
                    'TYPE' => $eventType
                ]);
                if (!$result->isSuccess())
                {
                    throw new AddEventAtpException($eventType, $this->year);
                }
            }
        }
    }

}
