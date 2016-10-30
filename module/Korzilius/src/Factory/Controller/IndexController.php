<?php

namespace Korzilius\Factory\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use KoBackbone\Service\BackboneService;
use Korzilius\Service\MessageService;

class IndexController implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \Korzilius\Controller\IndexController())
      ->setBackboneService($container->get(BackboneService::class))
      ->setMessageService($container->get(MessageService::class));
  }
}
