<?php
/**
 * Класс для экземпляра стадии стажировки
 */

namespace aton\tools\Atp\Stage;

use aton\tools\Tables\Atp\AtpStageTable;
use aton\Main\Type\DateTime as DateTime;

class Stage
{
    /**
     * @var array|null
     */
    protected $stage = [];

    public function __construct(array $stageDate)
    {
        $this->stage = $stageDate;
    }

    /**
     * Проверяет, активна ли по дате текущая стадия стажировки
     * @return bool
     */
    public function isActiveDate(): bool
    {
        /** @var DateTime $dateStart */
        $dateStart = $this->stage['DATE_START'] ?? null;
        /** @var DateTime $dateStart */
        $dateEnd = $this->stage['DATE_END'] ?? null;
        $now = new DateTime();

        if ($dateStart === null || $dateEnd === null) {
            return false;
        }

        return $dateStart <= $now && $now <= $dateEnd;
    }

    /**
     * Возвращает объект Стадии по его коду
     * @param string $code
     * @return static|null
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    static public function getStageByCode(string $code): ?static
    {
        $stageDate = AtpStageTable::getList([
            'filter' => ['CODE' => $code]
        ])->fetch();
        if(!$stageDate) {
            return null;
        }
        return new static($stageDate);
    }

}
