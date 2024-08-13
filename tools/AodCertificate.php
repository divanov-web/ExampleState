<?php
/**
 * Класс для создания сертификата AOD
 */

namespace aton\tools\Atp;


use aton\tools\Common\Phone\PhoneException\UnformattedPhoneException;
use aton\tools\Tables\Atp\AtpFileTable;
use aton\tools\Tables\Atp\AtpFileTypeTable;
use File;

class AodCertificate
{

    private AtpApplication $application;

    private string $dir = '/upload/trainee/aod/certificate/';

    private string $fullPath = '';
    private string $fullName = '';

    public function __construct(AtpApplication $application)
    {
        $this->application = $application;
        $this->fullPath = $_SERVER["DOCUMENT_ROOT"] . $this->dir;
        $this->fullName = $this->fullPath . 'cert_' . $application->getId() . '.pdf';
    }


    public function create(): int
    {
        $this->checkDir();

        $pageWidth = 290;
        $pageHeight = 205;

        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('L', 'mm', array($pageWidth, $pageHeight), true, 'UTF-8', false);

        // Загружаем существующий PDF
        $pdf->setSourceFile($_SERVER["DOCUMENT_ROOT"] . 'cr/images/aod_template.pdf');
        $pageNo = 1;
        $tplIdx = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx);
        // Устанавливаем шрифт
        $pdf->SetFont('circelight', '', 24);
        //// Устанавливаем белый цвет текста
        $pdf->SetTextColor(255, 255, 255);
        // Устанавливаем позицию текста
        $pdf->SetXY(20, 87);
        // Вставляем текст в поле и выравниваем по центру
        $pdf->MultiCell(200, 10,  $this->application->getFullName(), 0, 'L', false);
        // Сохраняем PDF файл в указанную директорию
        $pdf->Output($this->fullName, 'F');

        $arFile = File::MakeFileArray($this->fullName);
        $arFile['MODULE_ID'] = 'aton.tools';
        $offerFileId = File::SaveFile($arFile, 'trainee');
        $this->application->setOfferFile($offerFileId);
        $this->application->save();

        return $offerFileId;
    }

    /**
     * Создаёт директорию сертификата, если ещё не создана
     * @return void
     */
    private function checkDir(): void
    {
        if (!is_dir($this->fullPath)) {
            mkdir($this->fullPath, 0777, true);
        }
    }

}
