<?php

namespace Korzilius\Factory\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use Korzilius\Mapper\MessageMapper;

class MessageService implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \Korzilius\Service\MessageService())
      ->setMessageMapper($container->get(MessageMapper::class));
  }
}
