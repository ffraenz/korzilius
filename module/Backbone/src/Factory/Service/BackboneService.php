<?php

namespace Backbone\Factory\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class BackboneService implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    $backboneService = (new \Backbone\Service\BackboneService())
      ->configure($container->get('config'));

    // add cache adapter if available
    if ($container->has('memcached')) {
      $backboneService->setCacheAdapter($container->get('memcached'));
    }

    return $backboneService;
  }
}
