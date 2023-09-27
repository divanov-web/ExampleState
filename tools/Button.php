<?php

namespace aton\tools\Atp;


class Button
{
    protected $applicationId = null;
    /**
     * @var AtpApplication|null
     */
    protected $application = null;
    protected $text = '';
    protected $status = '';
    protected $isButton = false;
    protected $colorClass = '';

    public function __construct($text = '', $status = '')
    {
        $this->text = $text;
        $this->status = $status;
        if(strlen($status) > 0) {
            $this->isButton = true;
        }
    }

    public function show() {
        if($this->isButton) {
            $html = '<input type="button" class="adm-btn-repeat change_status ' . $this->colorClass . '" name="change_status"'.
                    ' data-status="' . $this->status . '" data-id="' . $this->applicationId . '"'.
                    ' value="' . $this->text . '" title="' . $this->text . '">';
        } else {
            $html = $this->text;
        }
        return $html;
    }

    public function greenColor() {
        $this->colorClass = 'green-btn';
    }

    public function redColor() {
        $this->colorClass = 'red-btn';
    }

    public function setApplicationId($id) {
        $this->applicationId = $id;
    }

    public function setApplication(AtpApplication $application) {
        $this->application = $application;
    }
}
