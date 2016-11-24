<?php

namespace Korzilius\Factory\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use Korzilius\Mapper\ClientMapper;
use Korzilius\Entity\EntityArrayHydrator;

class ClientResourceController implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \Korzilius\Controller\ClientResourceController())
      ->setClientMapper($container->get(ClientMapper::class))
      ->setHydrator($container->get(EntityArrayHydrator::class));
  }
}
