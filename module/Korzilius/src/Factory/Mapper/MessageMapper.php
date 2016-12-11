<?php

namespace Korzilius\Factory\Mapper;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use Korzilius\Mapper\UserMapper;

class MessageMapper implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \Korzilius\Mapper\MessageMapper())
      ->setUserMapper($container->get(UserMapper::class));
  }
}
