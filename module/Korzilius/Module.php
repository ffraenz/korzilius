<?php

namespace Korzilius;

use DateTime;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

use Korzilius\Entity\Client;
use Korzilius\Entity\Message;

class Module {

  protected $application;
  protected $eloUserIdUserMap;

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

      // push message received event to clients
      $hydrator = $serviceManager->get(Entity\EntityArrayHydrator::class);
      $webSocketService = $serviceManager->get(Service\WebSocketService::class);

      $webSocketService->pushEvent(
        'messageReceived', $hydrator->extract($message));

    } else {

    }
  }

  public function getUserByEloUserId($eloUserId) {
    $serviceManager = $this->application->getServiceManager();

    if ($this->eloUserIdUserMap === null) {
      $userMapper = $serviceManager->get(Mapper\UserMapper::class);
      $users = $userMapper->fetchAll();

      // map elo user ids to users
      $this->eloUserIdUserMap = [];
      foreach ($users as $user) {
        $this->eloUserIdUserMap[$user->getEloUserId()] = $user;
      }
    }

    return isset($this->eloUserIdUserMap[$eloUserId])
      ? $this->eloUserIdUserMap[$eloUserId]
      : null;
  }

  public function onBackboneDocumentUpdated(Event $event) {
    // get services
    $serviceManager = $this->application->getServiceManager();
    $clientMapper = $serviceManager->get(Mapper\ClientMapper::class);
    $messageMapper = $serviceManager->get(Mapper\MessageMapper::class);

    $data = $event->getParam('document');
    $documentId = (string) $data['id'];

    // mask field values to field names
    $maskFields = $data['mask']['fields'];
    $maskFieldValues = [];

    foreach ($maskFields as $maskField) {
      $maskFieldValues[$maskField['name']] = $maskField['value'];
    }

    // retrieve relevant client names from mask fields
    $clientNames = [];
    if (isset($maskFieldValues['txt_client_name1'])) {
      array_push($clientNames, $maskFieldValues['txt_client_name1']);
    }
    if (isset($maskFieldValues['txt_client2_name'])) {
      array_push($clientNames, $maskFieldValues['txt_client2_name']);
    }

    if (count($clientNames) === 0) {
      trigger_error(sprintf(
        '%s - Unable to determine relevant client names for document %d',
        __METHOD__,
        $documentId
      ), E_USER_NOTICE);
      return;
    }

    // match clients to this document
    $clients = $clientMapper->fetchAllByName($clientNames);
    if (count($clients) === 0) {
      trigger_error(sprintf(
        '%s - Unable to retrieve clients for document %d by name (%s)',
        __METHOD__,
        $documentId,
        implode(', ', $clientNames)
      ), E_USER_NOTICE);
      return;
    }

    // check if messages already exist for this document
    $messages = $messageMapper->fetchAllByTargetId($documentId);

    // map existing messages to their corresponding receiver
    $receiverMessageMap = [];
    foreach ($messages as $message){
      $receiverMessageMap[$message->getReceiverClientId()] = $message;
    }

    // collect message receiver ids
    $messageReceiverIds = array_map(function($client) {
      return $client->getId();
    }, $clients);

    // collect former and future message receiver for this document
    $receiverIds = array_unique(array_merge(
      $messageReceiverIds,
      array_keys($receiverMessageMap)
    ));

    // manage message for each receiver
    foreach ($receiverIds as $receiverId) {
      $message = isset($receiverMessageMap[$receiverId])
        ? $receiverMessageMap[$receiverId]
        : null;

      // check if this receiver should have a message for this document
      if (array_search($receiverId, $messageReceiverIds) !== false) {
        // create new message if not existing yet
        if ($message === null) {
          $sendTime = new DateTime();
          $sendTime->setTimestamp($data['createTime']);

          $message = (new Message())
            ->setSendTime($sendTime)
            ->setSenderUser($this->getUserByEloUserId($data['createUserId']))
            ->setDeliveredTime(new DateTime());
        }

        // update message and save
        $message
          ->setType('document')
          ->setReceiverClientId($receiverId)
          ->setTargetId($documentId)
          ->setText($data['title'])
          ->setMeta($data);

        $messageMapper->save($message);

      } else if ($message !== null) {
        // delete existing message
        $messageMapper->delete($message);
      }
    }
  }
}
