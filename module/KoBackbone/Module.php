<?php

namespace KoBackbone;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

class Module {

  protected $application;

  public function onBootstrap(MvcEvent $event) {
    $this->application = $event->getApplication();
    $sharedEvents = $this->application->getEventManager()->getSharedManager();

    $sharedEvents->attach(
      'KoFacebook\Service\WebhookService',
      'messageReceived',
      [$this, 'onFacebookMessageReceived']);
  }

  public function onFacebookMessageReceived(Event $event) {
    $serviceManager = $this->application->getServiceManager();

    $facebookService = $serviceManager->get('KoFacebook\Service\FacebookService');
    $config = $serviceManager->get('config');

    $pageAccessToken = $config['korzilius_facebook']['page_access_token'];

    $facebookService->create('/me/messages?access_token=' . $pageAccessToken, [
      'recipient' => [
        'id' => $event->getParam('userId'),
      ],
      'message' => [
        'text' => 'Echo! ' . $event->getParam('text'),
      ],
    ]);

    trigger_error(sprintf(
      '%s - Message recieved: %s',
      __METHOD__,
      $event->getParam('text')
    ), E_USER_NOTICE);
  }

  public function getAutoloaderConfig() {
    return [
      'Zend\Loader\StandardAutoloader' => [
        'namespaces' => [
          __NAMESPACE__ => __DIR__ . '/src/',
        ],
      ],
    ];
  }

  public function getConfig() {
    return include __DIR__ . '/config/module.config.php';
  }
}
