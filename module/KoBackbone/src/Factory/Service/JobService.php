<?php

namespace KoBackbone\Factory\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use KoBackbone\Service\BackboneService;

class JobService implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \KoBackbone\Service\JobService())
      ->setBackboneService($container->get(BackboneService::class))
      ->setPersistentCacheAdapter(
        $container->get('korzilius-backbone-persistent'));
  }
}
