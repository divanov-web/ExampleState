<?php
/**
 * Класс для управления событиями
 * Возвращает список программ. Создаёт новые программы, если их нет в базе.
 */

namespace aton\tools\Atp;

use aton\tools\Atp\AtpException\AddEventAtpException;
use aton\tools\Tables\Atp\AtpApplicationTable;
use aton\tools\Tables\Atp\AtpEventTable;
use \aton\Main\Application;
use \aton\Main\Localization\Loc;
use \aton\Main\UserConsent\Agreement;
use \aton\Main\UserConsent\Consent;
use \aton\Main\Type\DateTime;
use File;

class AtpEventController
{
    private $year = null;
    private $eventValues = [];
    private $events = [];
    /**
     * @var string  код инфоблока с лендингами карьеры
     */
    private string $iBlockCodeLanding = 'cr_landing';
    /**
     * @var string код раздела с описанием программ стажировок
     */
    private string $sectionCodeLanding = 'start_programs';

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

    /**
     * Создаёт массив текущих программ
     * @param bool $addLanding - нужно ли добавлять информацию из лендинга (название, картинги и тд)
     * @throws AddEventAtpException
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function __construct(bool $addLanding = false)
    {
        $this->year = date('Y');
        $this->events = $this->getActualEvents();
        if(empty($this->events) || count($this->events) != count($this->eventValues) ) {
            $this->createActualEvents();
            $this->events = $this->getActualEvents();
        }
        if($addLanding) {
            $this->addEventLanding();
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
            if($row['URL_CODE'] == 'opendoors') { //переименование программы
                $row['URL_CODE'] = 'aod';
            }
            $row['URL_REGISTRATION'] = "/start/".$row['URL_CODE']."/form/";
            $landingCodes[] = $row['TYPE'];
            $arEvents[$row['TYPE']] = $row;
        }



        return $arEvents;
    }

    /**
     * добавляет в массив программ информацию по программам из Лендинга
     * Название, картинки, описание
     * @return void
     */
    private function addEventLanding(): void {
        if(empty($this->events))
            return;
        $arEvents = $this->events;
        $arSelect = ["ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "CODE", "PREVIEW_TEXT", "PREVIEW_PICTURE"];
        $arFilter = ["IBLOCK_CODE" => $this->iBlockCodeLanding, 'SECTION_CODE' => $this->sectionCodeLanding];
        $res = \CIBlockElement::GetList(["SORT"=>"ASC"], $arFilter, false, false, $arSelect);
        while ($arItem = $res->Fetch()) {
            $code = $arItem['CODE'];
            if($arItem['CODE'] && $arEvents[$code]) {
                unset($arItem['ID']);
                unset($arItem['CODE']);
                if ($arItem['PREVIEW_PICTURE'] !== '') {
                    $arItem['PREVIEW_PICTURE'] = File::GetPath($arItem['PREVIEW_PICTURE']);
                }
                $arEvents[$code] = array_merge($arEvents[$code], $arItem);
            }
        }
        $this->events = $arEvents;
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
