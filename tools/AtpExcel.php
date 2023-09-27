<?php

namespace aton\tools\Atp;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;

class AtpExcel
{

    protected $items = [];
    protected $headers = [];

    public function __construct($items, $headers)
    {
        $this->items = $items;
        $this->headers = $headers;
    }

    public function getSpreadSheet() {
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()->setCreator('Aton Talent Program')
            ->setTitle('Aton Talent Program - UserList');

        $sharedStyleCell = new Style();

        $sharedStyleCell->applyFromArray(
            [
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_THIN],
                    'right' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]
        );

        $i = '1'; //строка
        $j = 'A'; //столбец

        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();
        foreach($this->headers as $head) {
            $activeSheet->setCellValue($j . $i, self::clearText($head['content']));
            $activeSheet->getColumnDimension($j)->setWidth(30);
            $j++;
        }
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $j--; //Возращаем столбец для стилей
        $activeSheet->duplicateStyle($sharedStyleCell, 'A' . $i . ':' . $j . $i);
        $activeSheet->getStyle('A' . $i . ':' . $j . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $i++;
        foreach($this->items as $item) {
            $j = 'A'; //столбец
            $item['STATUS_ID'] = $item['STATUS_VALUE'];
            foreach($this->headers as $head) {
                $fieldName = $head['id'];
                if($fieldName == 'TEST_RESULT') {
                    $value = $item['ATTEMPT_SCORE_PERCENT'] . '% ';
                    $value .= '(' . ($item['ATTEMPT_SCORE'] / 10) . ' из ' . ($item['ATTEMPT_MAX_SCORE'] / 10) . ')';
                } elseif($fieldName == 'ABOUT_INTERNET') {
                    if(!is_array($item[$fieldName])) {
                        $item[$fieldName] = $item[$fieldName] ? [$item[$fieldName]] : [];
                    }
                    if($item['ABOUT_OTHER']) {
                        $item[$fieldName][] = $item['ABOUT_OTHER'];
                    }
                    $value = implode(', ', $item[$fieldName]);
                } elseif(is_array($item[$fieldName])) {
                    $value = implode(', ', $item[$fieldName]);
                } else {
                    $value = $item[$fieldName];
                }
                $activeSheet->setCellValue($j . $i, self::clearText($value));
                $j++;
            }

            $i++;
        }

        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle('Users');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public static function clearText($text) {
        return str_replace(['&nbsp;'], ' ', $text);
    }

}