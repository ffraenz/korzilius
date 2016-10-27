<?php

namespace Application\Factory\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class IndexController implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \Application\Controller\IndexController())
      ->setBackboneService($container->get('Backbone\Service\Backbone'));
  }
}
