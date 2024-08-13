<?php
/**
 * Класс для выбранных файлов в анкете стажёра
 */

namespace aton\tools\Atp;


use aton\tools\Tables\Atp\AtpFileTable;
use aton\tools\Tables\Atp\AtpFileTypeTable;
use File;

class AtpAppFiles
{
    private ?array $files = [];
    private ?array $deletedFiles = [];

    private ?AtpApplication $application;

    /**
     * @return array|null
     */
    public function getFiles(): ?array
    {
        return $this->files;
    }

    /**
     * @return array|null
     */
    public function getDeletedFiles(): ?array
    {
        return $this->deletedFiles;
    }

    public function __construct(AtpApplication $application)
    {
        $this->application = $application;
        $this->files = $this->getItems();
        $this->deletedFiles = $this->getItems(false);
    }

    /**
     * @param bool $active
     * @return array
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     * @throws \aton\Main\SystemException
     */
    private function getItems(bool $active = true):array {
        $appItems = [];
        $filter = [
            'APPLICATION_ID' => $this->application->getId()
        ];
        $filter['ACTIVE'] = $active;
        $rsDataFile = AtpFileTable::getList([
            'order' => ['ID' => 'DESC'],
            'select' => ['ID', 'FILE_TYPE_ID', 'FILE_ID', 'CODE' => 'FILE_TYPE.CODE', 'NAME' => 'FILE_TYPE.NAME', 'TYPE' => 'FILE_TYPE.TYPE'],
            'filter' => $filter,
        ]);
        while ($rowFile = $rsDataFile->fetch()) {
            $arFile = File::GetFileArray($rowFile['FILE_ID']);
            $rowFile['PATH'] = $arFile['SRC'];
            $rowFile['ORIGINAL_NAME'] = $arFile['ORIGINAL_NAME'];
            if (!isset($appItems[$rowFile['CODE']])) { //создаём сгруппированный массив
                $appItems[$rowFile['CODE']] = [
                    'items' => [],
                    'name' => $rowFile['NAME'],
                    'type' => $rowFile['TYPE']
                ];
            }
            $appItems[$rowFile['CODE']]['items'][] = array_change_key_case($rowFile, CASE_LOWER);
        }
        return array_change_key_case($appItems, CASE_LOWER);
    }

    /**
     * Сохраняет файлы из POST только методами File для конкретной заявки на стажировку и возвращает их id
     * @param AtpApplication $application
     * @param $arFiles
     * @return array
     * @throws \aton\Main\ArgumentException
     * @throws \aton\Main\ObjectPropertyException
     */
    static public function saveFilesFromPost(AtpApplication $application, $arFiles): ?array
    {
        $fileTypes = [];
        $rsDataFile = AtpFileTypeTable::getList([
            'select' => ['*'],
            'filter' => ['EVENT_TYPE.CODE' => $application->getEventType()],
        ]);
        while ($rowFile = $rsDataFile->fetch()) {
            $fileTypes[] = $rowFile;
        }
        $result = [];
        foreach($fileTypes as $file) {
            $type = mb_strtolower($file['CODE']);
            $typeId = $file['ID'];
            $postFile = $arFiles[$type];
            if (isset($postFile) && is_array($postFile)) {
                foreach ($postFile['name'] as $key => $value) {
                    $fileArray = [
                        'name' => $postFile['name'][$key],
                        'size' => $postFile['size'][$key],
                        'tmp_name' => $postFile['tmp_name'][$key],
                        'type' => $postFile['type'][$key],
                        'error' => $postFile['error'][$key],
                        'MODULE_ID' => 'aton.tools',
                    ];

                    $fileId = File::SaveFile($fileArray, 'trainee');
                    if ($fileId > 0) {
                        $result[$typeId][] = $fileId;
                    }
                }
            }
        }
        return !empty($result) ? $result: null;
    }
}
