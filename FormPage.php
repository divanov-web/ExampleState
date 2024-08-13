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

class FormPage extends Controller
{
    function configureActions(): array
    {
        return [
            'getDataForm' => [
                'prefilters' => [
                    new Cache(),
                ],
                'postfilters' => [],
            ],
            'saveFormData' => [ //
                'prefilters' => [
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST])
                ],
            ],
        ];
    }

    /**
     * form data, fields and answers
     * @param $formSid
     * @return array
     * @throws \aton\Main\LoaderException
     */
    public function getDataFormAction($formSid)
    {
        $formData = Form::getDataForm($formSid);

        $response = [
            'formSid' => $formSid,
            'fields' => $formData['QUESTIONS'],
            'sessid' => sessid(),
        ];
        return $response;
    }

    /**
     * @param $formSid
     * @return array|null
     * @throws \aton\Main\LoaderException
     */
    public function saveFormDataAction($formSid)
    {
        $this->objRequest = Application::getInstance()->getContext()->getRequest();
        $arPost = $this->objRequest->getPostList()->toArray();

        $formId = CForm::GetBySID($formSid)->Fetch()['ID'];

        $arData = [];
        $formData = Form::getDataForm($formSid);

        $arErrors = \CForm::Check($formId, $arData, false, 'Y', 'Y');

        if ($arErrors) {
            foreach ($arErrors as $error) {
                $this->addError(new Error($error, 'validate_error'));
            }
            return $arData;
        }

        $cFormResult = new \CFormResult();
        $resId = $cFormResult->Add($formId, $arData);
        if ($resId) {
            $arData['resultId'] = $resId;
        } else {
            $this->addError(new Error(Loc::getMessage('FROM_EMAIL_ERROR'), 'form_error'));
            return null;
        }

        parent::saveFormDataAction();

        if ($this->arParams['ATP_ACTION'] == 'TASK') { //task complete form
            $this->application->changeStatusByCode(StatusTaskFinished::$statusCode);
            $this->application->changeTaskResult($this->response['RESULT']);
            $this->application->save();
        } else { //registration form
            $data = [
                'USER_ID' => $USER->GetID(),
                'EVENT_ID' => $arData['EVENT_ID'],
                'NAME' => trim($arData['LAST_NAME']) . ' ' . trim($arData['NAME']),
                'EMAIL' => trim($arData['EMAIL']),
                'RESULT_ID' => $this->response['RESULT'],
                'PHONE' => $arData['PHONE'],
                'CITIZENSHIP' => $arData['CITIZENSHIP'],
            ];

            if ($arData['BIRTHDAY'])
                $data['BIRTHDAY'] = new DateTime($arData['BIRTHDAY'], "d.m.Y");


            $application = new AtpApplication($data);
            $application->save();

            if (!$application->checkErrors()) {
                $errors = $application->getUserError()->getErrors();
                foreach ($errors as $error) {
                    $this->addError(new Error($error, 'validate_error'));
                }
                return null;
            } else {
                $application->sendEmail();
            }
        }

        return $application->getApplicationId();
    }

    protected function getEventData()
    {
        global $USER;

        $eventController = new AtpEventController();
        $events = $eventController->getEvents();
        $eventType = $this->arParams['ATP_EVENT_TYPE'];
        $this->response['EVENT'] = $events[$eventType];

        if ($this->application) {
            $this->response['APPLICATION'] = $this->application->getData();
        }

    }

}