<?php

namespace Korzilius\Factory\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use KoFacebook\Service\GraphService;
use Korzilius\Service\WebSocketService;
use Korzilius\Entity\EntityArrayHydrator;
use Korzilius\Mapper\MessageMapper;

class MessageService implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \Korzilius\Service\MessageService())
      ->setMessageMapper($container->get(MessageMapper::class))
      ->setHydrator($container->get(EntityArrayHydrator::class))
      ->setWebSocketService($container->get(WebSocketService::class))
      ->setGraphService($container->get(GraphService::class));
  }
}
