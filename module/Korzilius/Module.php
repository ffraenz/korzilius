<?php

namespace Korzilius;

use DateTime;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

use Korzilius\Entity\Client;
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
      'KoBackbone\Service\JobService',
      'clientUpdated',
      [$this, 'onBackboneClientUpdated']);

    $sharedEvents->attach(
      'KoBackbone\Service\JobService',
      'documentUpdated',
      [$this, 'onBackboneDocumentUpdated']);

    $sharedEvents->attach(
      'KoFacebook\Service\WebhookService',
      'messageReceived',
      [$this, 'onFacebookMessageReceived']);
  }

  public function onBackboneClientUpdated(Event $event) {
    // get services
    $serviceManager = $this->application->getServiceManager();
    $clientMapper = $serviceManager->get(Mapper\ClientMapper::class);
    $hydrator = $serviceManager->get(Entity\EntityArrayHydrator::class);

    $data = $event->getParam('client');

    // check if client has already been saved
    $client = $clientMapper->fetchSingleById($data['id']);
    $exists = ($client !== null);

    if (!$exists) {
      // create new client instance
      $client = new Client();
      $client->setId($data['id']);
    }

    // rename update time to sync time
    $data['syncTime'] = $data['updateTime'];
    unset($data['updateTime']);

    // update client entity
    $hydrator->hydrate($data, $client);

    // save client
    $clientMapper->save($client, $exists);
  }

  public function onFacebookMessageReceived(Event $event) {
    // get services
    $serviceManager = $this->application->getServiceManager();
    $clientMapper = $serviceManager->get(Mapper\ClientMapper::class);
    $messageMapper = $serviceManager->get(Mapper\MessageMapper::class);

    $sentByPage = $event->getParam('sentByPage');
    $facebookUserId = $event->getParam('userId');

    // match facebook user id to a client
    $client = $clientMapper->fetchSingleByFacebookUserId($facebookUserId);

    if ($client === null) {
      trigger_error(sprintf(
        '%s - Unrecognized facebook user id %d',
        __METHOD__,
        $facebookUserId
      ), E_USER_NOTICE);
      return;
    }

    if (!$sentByPage) {

      // create message
      $message = (new Message())
        ->setExternalId($event->getParam('id'))
        ->setType('facebook')
        ->setSendTime($event->getParam('time'))
        ->setDeliveredTime(new DateTime())
        ->setSenderClient($client)
        ->setText($event->getParam('text'));

      $messageMapper->save($message);

    } else {

    }
  }

  public function onBackboneDocumentUpdated(Event $event) {

  }
}
