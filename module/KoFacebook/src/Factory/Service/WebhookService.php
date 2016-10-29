<?php

namespace KoFacebook\Factory\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class WebhookService implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \KoFacebook\Service\WebhookService())
      ->configure($container->get('config'));
  }
}
