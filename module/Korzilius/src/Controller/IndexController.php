<?php

namespace Korzilius\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use KoBackbone\Service\BackboneService;

class IndexController extends AbstractActionController {

  protected $backboneService;

  public function getBackboneService() {
    return $this->backboneService;
  }

  public function setBackboneService(BackboneService $backboneService) {
    $this->backboneService = $backboneService;
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
