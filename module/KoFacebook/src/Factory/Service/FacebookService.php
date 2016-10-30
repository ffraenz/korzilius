<?php

namespace KoFacebook\Factory\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FacebookService implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \KoFacebook\Service\FacebookService())
      ->configure($container->get('config'));
  }
}
