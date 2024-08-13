<?php

namespace aton\tools\Atp;


class ButtonTaskFinished extends Button
{
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
