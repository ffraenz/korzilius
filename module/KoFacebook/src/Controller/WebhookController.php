<?php

namespace KoFacebook\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use KoFacebook\Service\WebhookService;
use KoFacebook\Exception\WebhookServiceException;
use KoFacebook\Entity\WebhookUpdate;

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
      // interpret this webhook request,
      // retrieve update object and prepare response
      $update = $this->getWebhookService()->handleRequest($request, $response);
    } catch (WebhookServiceException $exception) {
      // log error
      trigger_error(sprintf(
        '%s - WebhookServiceException thrown: %s',
        __METHOD__,
        $exception->getMessage()
      ), E_USER_NOTICE);

      // respond
      $response->setStatusCode($exception->getCode());
      return new JsonModel([
        'error' => true,
        'message' => $exception->getMessage(),
      ]);
    }

    if ($update !== null) {
      // handle update
    }

    // send back response prepared by webhook service
    return $response;
  }
}
