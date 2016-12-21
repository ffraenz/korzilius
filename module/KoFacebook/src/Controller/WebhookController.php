<?php

namespace KoFacebook\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use KoFacebook\Service\WebhookService;
use KoFacebook\Exception\WebhookServiceException;

class WebhookController extends AbstractActionController {

  protected $webhookService;

  public function getWebhookService() {
    return $this->webhookService;
  }

  public function setWebhookService(WebhookService $webhookService) {
    $this->webhookService = $webhookService;
    return $this;
  }

  public function indexAction() {
    $request = $this->getRequest();
    $response = $this->getResponse();

    try {
      // interpret this webhook request, trigger an event and respond to it
      return $this->getWebhookService()->handleRequest($request, $response);
    } catch (WebhookServiceException $exception) {
      // log error
      trigger_error(sprintf(
        '%s - WebhookServiceException thrown: %s',
        __METHOD__,
        $exception->getMessage()
      ), E_USER_WARNING);

      // respond
      $response->setStatusCode($exception->getCode());
      return new JsonModel([
        'error' => [
          'message' => $exception->getMessage(),
          'code' => $exception->getCode(),
        ],
      ]);
    }
  }
}
