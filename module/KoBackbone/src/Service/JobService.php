<?php

namespace KoBackbone\Service;

use DateTime;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

class JobService implements EventManagerAwareInterface {

  protected $events;
  protected $backboneService;

  const PAGE_RESOURCE_COUNT = 50;

  public function getEventManager() {
    if ($this->events === null) {
      $this->setEventManager(new EventManager());
    }
    return $this->events;
  }

  public function setEventManager(EventManagerInterface $events) {
    $events->setIdentifiers([__CLASS__, get_called_class()]);
    $this->events = $events;
    return $this;
  }

  public function getBackboneService() {
    return $this->backboneService;
  }

  public function setBackboneService(BackboneService $backboneService) {
    $this->backboneService = $backboneService;
    return $this;
  }

  public function updateDocuments($all = false) {
    $parameters = [];

    if (!$all) {
      // TODO: How to store the last update timestamp?
      // $parameters['updated_since'] = null;
    }

    $this->fetchResources(
      '/documents', $parameters, [$this, 'handleDocumentUpdated']);
  }

  public function updateClients($all = false) {
    $parameters = [];

    if (!$all) {
      // TODO: How to store the last update timestamp?
      // $parameters['updated_since'] = null;
    }

    $this->fetchResources(
      '/clients', $parameters, [$this, 'handleClientUpdated']);
  }

  protected function handleDocumentUpdated($document) {
    $this->getEventManager()->trigger('documentUpdated', $this, [
      'document' => $document,
    ]);
  }

  protected function handleClientUpdated($client) {
    $this->getEventManager()->trigger('clientUpdated', $this, [
      'client' => $client,
    ]);
  }

  protected function fetchResources(
    $collectionPath, array $parameters, callable $handler
  ) {
    $count = self::PAGE_RESOURCE_COUNT;
    $offset = 0;

    // do not fetch all resources at once, fetch them page by page
    do {
      // fetch page of resources
      $resources = $this->getBackboneService()->get(
        '/documents',
        array_merge($parameters, [
          'offset' => $offset,
          'count' => $count,
        ])
      );

      // handle each updated resource
      foreach ($resources as $resource) {
        $handler($resource);
      }

      // set offset for next request
      $offset += $count;

      // log
      trigger_error(sprintf(
        '%s - Fetching resource updates for %s (%d)',
        __METHOD__,
        $collectionPath,
        $offset
      ), E_USER_NOTICE);

    } while (count($resources) === $count);
  }
}
