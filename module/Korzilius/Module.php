<?php

namespace Korzilius;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

use Korzilius\Entity\Client;

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

    // listen for client resource updates
    $sharedEvents->attach(
      'KoBackbone\Service\JobService',
      'clientUpdated',
      [$this, 'onBackboneClientUpdated']);
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
}
