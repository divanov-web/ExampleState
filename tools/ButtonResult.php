<?php

namespace aton\tools\Atp;


class ButtonResult extends Button
{

    public function show()
    {
        ob_start();
        ?>
        <div class="tooltip-container atp_first_day">
            <input type="button" class="adm-btn-repeat tooltip-item <?= $this->colorClass ?>"
                   data-status="<?= $this->status ?>" data-id="<?= $this->applicationId ?>"
                   value="<?= $this->text ?>" title="<?= $this->text ?>"/>
            <div class="tooltip-bubble">
                <div class="first_day_box">
                    <div class="title">Выбрать первый рабочий день:</div>
                    <div class="item_row">
                        <? $firstDate = date('01.07.Y 08:30:00')?>
                        <input type="text" class="adm-input adm-input-calendar ui-ctl-element" placeholder="<?= $firstDate ?>" name="first_day[<?=$this->applicationId?>]" <?
                        ?>id="first_day_id" value="<?= $firstDate ?>" />
                        <span class="adm-calendar-icon" title="Перывй рабочий день" onclick="BX.calendar({node:this, field:'first_day[<?=$this->applicationId?>]', form: '', bTime: true, bHideTime: false});"></span>
                    </div>
                    <input type="button" class="adm-btn-repeat change_status green-btn'"
                           data-status="" data-id=""
                           value="Подтвердить" title="Подтвердить"/>
                </div>
            </div>
        </div>
        <?
        $html = ob_get_clean();
        return $html;
    }

}
