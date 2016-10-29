<?php

namespace KoFacebook\Factory\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class WebhookController implements FactoryInterface {

  public function __invoke(
    ContainerInterface $container, $requestedName, array $options = null
  ) {
    return (new \KoFacebook\Controller\WebhookController())
      ->setWebhookService($container->get('KoFacebook\Service\WebhookService'));
  }
}
