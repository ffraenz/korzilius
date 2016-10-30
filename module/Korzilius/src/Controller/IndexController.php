<?php

namespace Korzilius\Controller;

use DateTime;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use KoBackbone\Service\BackboneService;
use Korzilius\Service\MessageService;
use Korzilius\Entity\Message;

class IndexController extends AbstractActionController {

  protected $backboneService;
  protected $messageService;

  public function getBackboneService() {
    return $this->backboneService;
  }

  public function setBackboneService(BackboneService $backboneService) {
    $this->backboneService = $backboneService;
    return $this;
  }

  public function getMessageService() {
    return $this->messageService;
  }

  public function setMessageService(MessageService $messageService) {
    $this->messageService = $messageService;
    return $this;
  }

  public function indexAction() {
    $client = $this->getBackboneService()->get('/clients/100660');

    echo '<pre>';
    var_dump($client);
    echo '</pre>';

    return new ViewModel();
  }
}
