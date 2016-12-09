<?php

namespace Korzilius\Factory\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use Korzilius\Mapper\MessageMapper;
use Korzilius\Mapper\ClientMapper;
use Korzilius\Entity\EntityArrayHydrator;

class MessageResourceController implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \Korzilius\Controller\MessageResourceController())
      ->setMessageMapper($container->get(MessageMapper::class))
      ->setClientMapper($container->get(ClientMapper::class))
      ->setHydrator($container->get(EntityArrayHydrator::class));
  }
}
