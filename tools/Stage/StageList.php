<?php
/**
 * Класс для экземпляра стадии стажировки для вывода во фронт
 */

namespace aton\tools\Atp\Stage;

use aton\tools\Tables\Atp\AtpStageTable;
use aton\Main\Type\DateTime as DateTime;

class StageList
{
    /**
     * Массив стадий из БЛ
     * @var array|null
     */
    private ?array $stages = [];

    /**
     * id текущей стадии
     * @var int|null
     */
    private ?int $currentStageId = null;

    /**
     * @var bool - Если true то стадии меняются по датам, если false то стадии меняются по статусам самой анкеты стажёра
     */
    private bool $stageByDate = false;

    /**
     * Создаёт экземпляр списка стадий
     * @param string $type
     * @param int|null $currentStageId
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function __construct(string $type, int $currentStageId = null, bool $stageByDate = false)
    {
        $this->stageByDate = $stageByDate;
        $this->stages = self::getStages($type);
        $this->currentStageId = $currentStageId;
    }

    public function getPreparedStages()
    {
        $result = [];
        $now = new DateTime();
        foreach ($this->stages as $stage) {
            $selected = false;
            //Определение активной стадии по разным вариантам
            if($this->stageByDate) {
                if ($stage['DATE_START'] && $stage['DATE_END'])
                    $selected = $stage['DATE_START'] <= $now && $now <= $stage['DATE_END'];
            } else {
                $selected = $stage['ID'] == $this->currentStageId;
            }

            $result[] = [
                'code' => $stage['CODE'],
                'name' => $stage['VALUE'],
                'date_start' => $stage['DATE_START'],
                'date_end' => $stage['DATE_END'],
                'selected' => $selected,
            ];
        }

        return $result;

    }

    /**
     * Возвращает список всех стадий стажировки
     * @param string $type
     * @return array|null
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    static public function getStages(string $type): ?array
    {
        $events = null;
        $code = \aton\tools\Atp\Utils\Stage::getProgramCodeByType($type);
        $result = AtpStageTable::getList([
            'order' => ['DATE_START' => 'ASC', 'ID' => 'ASC'],
            'filter' => [
                'EVENT_TYPE.CODE' => $code
            ]
        ]);
        while ($row = $result->fetch()) {
            $events[] = $row;
        }
        return $events;
    }
}
