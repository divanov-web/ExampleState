<?php

namespace aton\tools\Atp;

use aton\tools\Tables\AFormResultAnswerTable;
use aton\tools\Tables\AtpApplicationFilesTable;
use aton\tools\Tables\AtpApplicationTable;
use \aton\tools\Common\Form\AForm\AFormResult;
use \aton\Main\Entity\ExpressionField;
use File;

class AtpApplicationController
{
    private $year = null;
    private $applicationList = [];
    private $sTableID = '';
    private $showAllPages = false;
    /**
     * @var \CAdminResult|null
     */
    private $rsData = null;

    /**
     * @return array
     */
    public function getApplicationList(): array
    {
        return $this->applicationList;
    }

    /**
     * @return \CAdminResult
     */
    public function getRsData()
    {
        return $this->rsData;
    }

    /**
     * @param bool $showAllPages
     */
    public function setShowAllPages(bool $showAllPages): void
    {
        $this->showAllPages = $showAllPages;
    }



    public function __construct(string $sTableID)
    {
        $this->year = date('Y');
        $this->sTableID = $sTableID;
        //$this->applicationList = $this->getApplications();
    }

    /**
     * Get application for old admin list
     * @param $lAdmin
     * @param array $params
     * @param int $formId
     * @return array
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    public function getApplications(&$lAdmin, $params = [], $formId = false) {
        //\aton\Main\Application::getConnection()->startTracker();
        $arItems = [];
        //grouping for page navigation by USER_ID
        $resultIds = [];
        $resultForNav = AtpApplicationTable::getList([
            'order' => $params['order'] ?? ['ID' => 'DESC'],
            'select' => [
                'NAME', 'RESULT_ID', 'CNT',
                'ATTEMPT_DATE_START' => 'ATTEMPT.DATE_START',
                'ATTEMPT_MAX_SCORE' => 'ATTEMPT.MAX_SCORE',
                'ATTEMPT_SCORE' => 'ATTEMPT.SCORE'
                ],
            'filter' => $params['filter'] ?? [],
            'runtime' => array(
                new ExpressionField('CNT', 'COUNT(*)'),
                new ExpressionField('ID', 'MIN(aton_tools_tables_atp_application.ID)')
            ),
        ]);

        $rsDataNav = new \CAdminResult($resultForNav, $this->sTableID);
        if(!$this->showAllPages) {
            $rsDataNav->NavStart();
            $lAdmin->NavText($rsDataNav->GetNavPrint('page'));
        }
        while ($row = $rsDataNav->fetch())
        {
            $resultIds[] = $row['RESULT_ID'];
        }
        //endregion

        $selectJoin = [
            'EVENT_YEAR' => 'EVENT.YEAR',
            'EVENT_TYPE' => 'EVENT.TYPE',
            'USER_NAME' => 'USER.NAME',
            'USER_LAST_NAME' => 'USER.LAST_NAME',
            'USER_SECOND_NAME' => 'USER.SECOND_NAME',
            'USER_EMAIL' => 'USER.EMAIL',
            'STATUS_CODE' => 'STATUS.CODE',
            'STATUS_VALUE' => 'STATUS.VALUE',
            'ATTEMPT_DATE_START' => 'ATTEMPT.DATE_START',
            'ATTEMPT_MAX_SCORE' => 'ATTEMPT.MAX_SCORE',
            'ATTEMPT_SCORE' => 'ATTEMPT.SCORE',
            'APP_FILE_NAME' => 'APP_FILE.UF_NAME',
        ];

        $params['filter']['RESULT_ID'] = $resultIds ?: false;

        $result = AtpApplicationTable::getList([
            'order' => $params['order'] ?? ['ID' => 'DESC'],
            'select' => array_merge(["*"], $selectJoin),
            'filter' => $params['filter'],
        ]);
        $rsData = new \CAdminResult($result, $this->sTableID);

        $resultMapping = [];
        while ($row = $rsData->fetch())
        {
            $row['USER_FULL_NAME'] = self::getFullName($row);

            $row['ATTEMPT_SCORE_PERCENT'] =  intval($row['ATTEMPT_SCORE'] * 100 /  $row['ATTEMPT_MAX_SCORE']);
            //get files if task answers exist
            if($row['TEST_FILE_ID']) {
                $formResultId = $row['TEST_FILE_ID'];
                $arrVALUES = AFormResult::GetDataByIDForHTML($formResultId, 'Y');
                $arTaskFiles = [];
                foreach(array_keys($arrVALUES) as $key => $fieldName) {
                    if (stristr($fieldName, 'form_file_')) {
                        $answerId = str_replace('form_file_', '', $fieldName);
                        $arAnswerFile = AFormResult::GetFileByAnswerID($row['TEST_FILE_ID'], $answerId);
                        $arFile = File::GetFileArray($arAnswerFile["USER_FILE_ID"]);
                        $fileExtension = pathinfo($arFile['FILE_NAME'], PATHINFO_EXTENSION);
                        $filename = str_replace(' ', '_', $row['NAME']) . '_' . str_replace(' ', '_', $row['APP_FILE_NAME'])
                            . ($key > 0 ? '_' . $key : '') . '.' . $fileExtension;
                        $htmlFile = '<a target="_blank"  href="'.$arFile['SRC'].'" download="' . $filename . '">'.$filename.'</a>';
                        $arTaskFiles[] = $htmlFile;
                    }
                }
                $row['FILES'] = implode('<br />', $arTaskFiles);
            }
            $resultMapping[$row['RESULT_ID']] =  $row['ID'];
            $arItems[$row['ID']] = $row;
        }

        $this->rsData = $rsData;
        return $arItems;
    }

    public static function getFullName($row) {
        $fullName = $row['USER_LAST_NAME'];
        $fullName .= $row['USER_NAME'] ? ' ' . $row['USER_NAME'] : '';
        $fullName .= $row['USER_SECOND_NAME'] ? ' ' . $row['USER_SECOND_NAME'] : '';
        return $fullName;
    }
}
