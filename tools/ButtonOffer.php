<?php

namespace aton\tools\Atp;


class ButtonOffer extends Button
{

    public function show()
    {
        ob_start();
        ?>
        <div class="tooltip-container atp_offer">
            <input type="button" class="adm-btn-repeat tooltip-item <?= $this->colorClass ?>"
                   data-status="<?= $this->status ?>" data-id="<?= $this->applicationId ?>"
                   value="<?= $this->text ?>" title="<?= $this->text ?>"/>
            <div class="tooltip-bubble">
                <div class="offer_box">
                    <div class="title">Прикрепить файл с оффером:</div>
                    <div class="file_row">
                        <input type="file" name="offer_file_<?=$this->applicationId?>"/>
                    </div>
                    <input type="hidden" name="is_offer[<?=$this->applicationId?>]" value="1">
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
