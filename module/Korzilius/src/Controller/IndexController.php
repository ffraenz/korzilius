<?php

namespace Korzilius\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {

  public function indexAction() {
    $viewModel = new ViewModel();
    $viewModel->setTemplate('korzilius/index/index.phtml');
    return $viewModel;
  }
}
