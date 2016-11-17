<?php

namespace KoBackbone\Factory\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

use KoBackbone\Service\JobService;

class JobController implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \KoBackbone\Controller\JobController())
      ->setJobService($container->get(JobService::class))
      ->configure($container->get('config'));
  }
}
