<?php

namespace KoBackbone;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

use KoFacebook\Service\GraphService;

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
    $graph = $serviceManager->get('KoFacebook\Service\GraphService');

    $text = $event->getParam('text');
    $userId = $event->getParam('userId');

    if (in_array($text, ['mark_seen', 'typing_on', 'typing_off'])) {
      $graph->createMessageSenderAction($userId, $text);
    } else {
      // echo message back
      $graph->createMessage($userId, [ 'text' => $text ]);
    }

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
