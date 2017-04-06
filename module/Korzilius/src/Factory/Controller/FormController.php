<?php

namespace Korzilius\Factory\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use Korzilius\Mapper\ClientMapper;
use Korzilius\Service\FormService;

class FormController implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \Korzilius\Controller\FormController())
      ->setClientMapper($container->get(ClientMapper::class))
      ->setFormService($container->get(FormService::class));
  }
}
