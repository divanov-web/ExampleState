<?php
/**
 * Класс для выбранных департаментов
 */

namespace aton\tools\Atp\Department;


use aton\tools\Atp\AtpApplication;
use aton\tools\Atp\AtpException\SaveFormAtpDepException;
use aton\tools\Atp\Status\Atp\StatusAtpDepartments;
use aton\tools\Atp\Status\Atp\StatusNew;
use aton\tools\Atp\Utils\Status;
use aton\tools\Tables\Atp\AtpDepartmentAppTable;
use aton\tools\Tables\Atp\AtpDepartmentTable;
use aton\tools\Tables\Atp\AtpFileTypeTable;
use aton\Main\ArgumentException;
use aton\Main\ObjectPropertyException;
use aton\Main\SystemException;

class AtpAppDepartments
{
    private ?array $departments = [];

    private ?AtpApplication $application;

    /**
     * @return array|null
     */
    public function getDepartments(): ?array
    {
        return $this->departments;
    }

    /**
     * Получить список департаментов для первого статуса в зависимосте от типа стажировки
     * @param bool $onlyNames
     * @return array|null
     */
    public function getFirstStatusDepartments(bool $onlyNames = false): ?array
    {
        $firstStatus = Status::getFirstStatusCode($this->application->getEventType());
        $departments = $this->departments[$firstStatus];
        if($onlyNames) {
            $departments =  is_array($departments) ? array_column($departments, 'name') : null;
        }
        return $departments;
    }

    /**
     * Получить список департаментов для статуса выбора департамента
     * @param bool $onlyNames
     * @return array|null
     */
    public function getClassesStatusDepartments(bool $onlyNames = false): ?array
    {
        $departments = $this->departments[StatusAtpDepartments::$statusCode];
        if($onlyNames) {
            $departments =  is_array($departments) ? array_column($departments, 'name') : null;
        }
        return $departments;
    }

    public function __construct(AtpApplication $application)
    {
        $this->application = $application;
        $this->getItems();
    }

    private function getItems() {
        $appDepartments = [];
        $rsDataFile = AtpDepartmentAppTable::getList([
            'select' => ['*', 'NAME' => 'DEPARTMENT.NAME', 'STATUS_CODE' => 'DEPARTMENT.STATUS.CODE'],
            'filter' => ['APPLICATION_ID' => $this->application->getId(), 'ACTIVE' => 'Y'],
        ]);
        while ($rowFile = $rsDataFile->fetch()) {
            $appDepartments[$rowFile['STATUS_CODE']][] = [
                'row_id' => $rowFile['ID'],
                'id' => $rowFile['DEPARTMENT_ID'],
                'name' => $rowFile['NAME'],
            ];;
        }
        $this->departments = $appDepartments;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SaveFormAtpDepException
     * @throws ArgumentException
     * @throws SystemException
     */
    public function saveDepartments(array $newDepartments, string $statusCode): ?array
    {
        $application = $this->application;
        //Список существующих департаментов
        $departmentsID = [];
        $rsDataFile = AtpDepartmentTable::getList([
            'select' => ['ID'],
            'filter' => ['STATUS.CODE' => $statusCode],
        ]);
        while ($rowItem = $rsDataFile->fetch()) {
            $departmentsID[] = $rowItem['ID'];
        }

        //Уже привязанные департаменты к заявке
        $oldDepartmentsID = []; //Список старых айдишников, которые не добавляем
        $oldDepartments = AtpDepartmentAppTable::getList([
            'select' => ['*'],
            'filter' => ['DEPARTMENT.STATUS.CODE' => $statusCode, 'APPLICATION_ID' => $application->getId()],
        ])->fetchCollection();
        foreach($oldDepartments as $oldDepartment) {
            if(!in_array($oldDepartment->getDepartmentId(), $newDepartments)) {
                $oldDepartment->set('ACTIVE', false);
            } elseif ($oldDepartment->get('ACTIVE')) {
                $oldDepartmentsID[] = $oldDepartment->getDepartmentId();
            }
        }
        $oldDepartments->save();

        //Новые департаменты. ДОбавляем только те, что не было ранее
        foreach ($newDepartments as $departmentId) {
            //чтобы добавить новый департамент для анкеты, нужно чтобы он не был добавлен ранее и был в списке для текущего статуса
            if(!in_array($departmentId, $oldDepartmentsID) && in_array($departmentId, $departmentsID)) {
                $data = [
                    'DEPARTMENT_ID' => $departmentId,
                    'APPLICATION_ID' => $application->getId(),
                ];
                $res = AtpDepartmentAppTable::add($data);
                if(!$res) {
                    throw new SaveFormAtpDepException($departmentId);
                }
            }
        }


        return [];
    }
}
