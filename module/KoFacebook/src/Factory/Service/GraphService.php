<?php

namespace KoFacebook\Factory\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class GraphService implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \KoFacebook\Service\GraphService())
      ->configure($container->get('config'));
  }
}
