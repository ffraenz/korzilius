<?php

namespace Korzilius;

use DateTime;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

use KoFacebook\Service\GraphService;
use Korzilius\Service\MessageService;
use Korzilius\Entity\Message;

class Module {

  protected $application;

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

  public function onBootstrap(MvcEvent $event) {
    $this->application = $event->getApplication();
    $sharedEvents = $this->application->getEventManager()->getSharedManager();

    $sharedEvents->attach(
      'KoFacebook\Service\WebhookService',
      'messageReceived',
      [$this, 'onFacebookMessageReceived']);
  }

  public function onFacebookMessageReceived(Event $event) {
    // pull services
    $serviceManager = $this->application->getServiceManager();
    $messageService = $serviceManager->get(MessageService::class);

    // match this facebook user id to a client
    // $client = $clientMapper->fetchSingleWithFacebookUserId(
    //   $event->getParam('userId'));
    $client = null;

    // compose facebook message
    $message = (new Message())
      ->setExternalId($event->getParam('id'))
      ->setType('facebook')
      ->setSendTime($event->getParam('time'))
      ->setText($event->getParam('text'));

    if ($event->getParam('isEcho') === false) {
      // message from client
      $message
        ->setSenderClient($client)
        ->setDeliveredTime(new DateTime());
    } else {
      // message from page
      $message
        ->setReceiverClient($client);
    }

    $messageService->send($message);

    if ($event->getParam('isEcho') === false) {
      // echo message
      $graph = $serviceManager->get(GraphService::class);
      $text = $event->getParam('text');
      $userId = $event->getParam('userId');
      $graph->createMessage($userId, [ 'text' => $text ]);
    }

    trigger_error(sprintf(
      '%s - Message recieved: %s',
      __METHOD__,
      $event->getParam('text')
    ), E_USER_NOTICE);
  }
}
