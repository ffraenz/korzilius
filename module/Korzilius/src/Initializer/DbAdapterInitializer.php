<?php

namespace Korzilius\Initializer;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Initializer\InitializerInterface;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;

class DbAdapterInitializer implements InitializerInterface
{

  public function __invoke(ContainerInterface $container, $instance) {
    if ($instance instanceof AdapterAwareInterface) {
      $instance->setDbAdapter($container->get(Adapter::class));
    }
  }
}
