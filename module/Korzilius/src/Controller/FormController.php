<?php

namespace Korzilius\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use Korzilius\Exception\FormServiceException;
use Korzilius\Mapper\ClientMapper;
use Korzilius\Service\FormService;

class FormController extends AbstractActionController {

  protected $formService;
  protected $clientMapper;

  public function getFormService() {
    return $this->formService;
  }

  public function setFormService(FormService $formService) {
    $this->formService = $formService;
    return $this;
  }

  public function getClientMapper() {
    return $this->clientMapper;
  }

  public function setClientMapper(ClientMapper $clientMapper) {
    $this->clientMapper = $clientMapper;
    return $this;
  }

  public function createAction() {
    $clientId = $this->params()->fromRoute('client_id');

    // retrieve client
    $client = $this->getClientMapper()->fetchSingleById($clientId);
    if ($client === null) {
      $this->getResponse()->setStatusCode(404);
      return new JsonModel();
    }

    // retrieve form name & data
    $formName = $this->params()->fromRoute('form_name');
    $data = $this->params()->fromQuery();
    $data['client'] = $client;

    $filename = null;

    try {
      // create form
      $filename = $this->getFormService()->createForm($formName, $data);

    } catch (FormServiceException $exception) {

      // log error
      trigger_error(sprintf(
        '%s - FormServiceException thrown: %s',
        __METHOD__,
        $exception->getMessage()
      ), E_USER_WARNING);

      // respond
      $this->getResponse()->setStatusCode($exception->getCode());
      return new JsonModel([
        'error' => [
          'type' => get_class($exception),
          'message' => $exception->getMessage(),
          'code' => $exception->getCode(),
        ],
      ]);
    }

    // TODO: Do fun stuff with the $filename file!

    return new JsonModel([
      'success' => true,
    ]);
  }
}
