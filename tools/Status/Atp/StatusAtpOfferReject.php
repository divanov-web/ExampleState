<?php

namespace aton\tools\Atp\Status\Atp;

use aton\tools\Atp\Button;
use aton\tools\Atp\Status\AbstractStatus;
use aton\tools\Atp\Status\StatusInvited;

class StatusAtpOfferReject extends AbstractStatus
{
    static public $statusCode = 'ATP_OFFER_REJECTED';
    #static public $eventCode = 'ATP_REJECTED_MAIN';
    protected $rejectedStatus = true;

    public function getNextStatus(): ?AbstractStatus {
        return new StatusAtpOffer();
    }

    public function getRejectStatus(): ?AbstractStatus {
        return null;
    }

    public function getButton(): ?Button {
        return new Button($this->getButtonValue(), $this->getStatusCode());
    }

}
