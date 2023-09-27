<?php

namespace aton\tools\Atp;


class ButtonTask extends Button
{

    public function show() {
        ob_start();
        ?>
        <div class="tooltip-container">
            <input type="button" class="adm-btn-repeat tooltip-item <?=$this->colorClass ?>"
               data-status="<?=$this->status?>" data-id="<?=$this->applicationId ?>"
               value="<?=$this->text?>" title="<?=$this->text?>" />
            <div class="tooltip-bubble">

            </div>
        </div>
        <?
        $html = ob_get_clean();
        return $html;
    }

}
