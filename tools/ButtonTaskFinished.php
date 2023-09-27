<?php

namespace aton\tools\Atp;


class ButtonTaskFinished extends Button
{

    /*public function show() {
        ob_start();
        ?>
        <div class="tooltip-container">
            <input type="button" class="adm-btn-repeat tooltip-item <?=$this->colorClass ?>"
               value="<?=$this->text?>" title="<?=$this->text?>" />
            <div class="tooltip-bubble">
                <div class="title">Файл с ответами на ТЗ:</div>
                <div class="file-input">
                    <input type="file" id="fileboxf" name="taskFile" value="" />
                </div>
                <input type="button" class="adm-btn-repeat change_status task_send green-btn'"
                       data-status="<?=$this->status?>" data-id="<?=$this->applicationId?>"
                       value="Отправить" title="Отправить" />
            </div>
        </div>
        <?
        $html = ob_get_clean();
        return $html;
    }*/

    public function show() {
        $application = $this->application->getData();
        ob_start();
        ?>
        <a href="aton_tools_atp_addfile.php?ID=<?=$this->applicationId?>&APP_FILE_ID=<?=$application['APP_FILE_ID']?>" target="_blank">
            <input type="button" class="adm-btn-repeat tooltip-item <?=$this->colorClass ?>"
                   value="<?=$this->text?>" title="<?=$this->text?>" />
        </a>
        <?
        $html = ob_get_clean();
        return $html;
    }

}
