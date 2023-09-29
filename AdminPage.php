<?php

namespace aton\tools\Controller;

use aton\tools\Atp\AtpApplication;
use aton\tools\Atp\AtpEventController;
use aton\tools\Common\Form\Validation;
use aton\Main\Application;
use aton\Main\Engine\ActionFilter;
use aton\tools\Common\Form\Form;
use aton\Main\Engine\Controller;
use aton\Main\Error;
use aton\Main\Loader;
use aton\Main\Localization\Loc;
use CForm;
use DateTime;
use CFormAnswer;
use CFormField;
use CFormResult;
use general\tools\Controller\ActionFilter\CacheIBlock;

class FormAtp extends Controller
{
    function configureActions(): array
    {
        return [
            'getApplications' => [
                'prefilters' => [],
                'postfilters' => [],
            ],
        ];
    }

    public function getApplicationsAction($formSid)
    {
        $applicationController = new AtpApplicationController($sTableID);
        $items = $applicationController->getApplications($lAdmin, $params, $formId);

        foreach($items as $arRes) {
            $application = new AtpApplication($arRes);
            $arRes["NEXT_STATUS"] = $application->getNextButton();
            $arRes["REJECT_STATUS"] = $application->getRejectButton();
            $response[] = $arRes;
        }

        return $response;
    }


}