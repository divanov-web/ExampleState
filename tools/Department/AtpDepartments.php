<?php
/**
 * Класс для существующих департаментов
 */

namespace aton\tools\Atp\Department;

use aton\tools\Atp\Status\Atp\StatusAtpDepartments;
use aton\tools\Atp\Utils\Status;
use aton\tools\Tables\Atp\AtpDepartmentTable;

class AtpDepartments
{
    private string $eventType;
    private ?array $departments = [];

    /**
     * Получить список департаментов для первого статуса в зависимосте от типа стажировки
     * @return array|null
     */
    public function getFirstStatusDepartments(): ?array
    {
        $firstStatus = Status::getFirstStatusCode($this->eventType);
        return $this->departments[$firstStatus];
    }

    /**
     * Получить список департаментов для статуса выбора департамента
     * @return array|null
     */
    public function getClassesStatusDepartments(array $selectedDepartments = null): ?array
    {
        return $this->compareWithSelected($this->departments[StatusAtpDepartments::$statusCode], $selectedDepartments);
    }

    public function compareWithSelected(array $allDepartments, array $selectedDepartments = null): ?array
    {
        if ($selectedDepartments === null) {
            return $allDepartments;
        }
        // Создаем массив с id выбранных департаментов для быстрого поиска
        $selectedIds = array_column($selectedDepartments, 'id');
        foreach ($allDepartments as &$department) {
            if (in_array($department['id'], $selectedIds)) {
                $department['selected'] = true;
            }
        }

        return $allDepartments;
    }

    /**
     * Получить список департаментов для задонного статуса
     * @param string $code - код статуса
     * @return array|null
     */
    public function getDepartmentsByStatusCode(string $code): ?array
    {
        return $this->departments[$code];
    }


    public function __construct($eventType)
    {
        $this->eventType = $eventType;
        $this->loadItems();
    }

    /**
     * Запросить все департаменты из базы и сгруппировать их по коду статуса
     * @return void
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    private function loadItems() {
        $appDepartments = [];
        $rsDataFile = AtpDepartmentTable::getList([
            'select' => ['ID', 'NAME', 'STATUS_CODE' => 'STATUS.CODE'],
        ]);
        while ($rowFile = $rsDataFile->fetch()) {
            $appDepartments[$rowFile['STATUS_CODE']][] = [
                'id' => $rowFile['ID'],
                'name' => $rowFile['NAME'],
            ];
        }
        $this->departments = $appDepartments;
    }
}
