<?php

namespace KoBackbone\Service;

use DateTime;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Cache\Storage\Adapter\AbstractAdapter;

class JobService implements EventManagerAwareInterface {

  protected $events;
  protected $persistentCacheAdapter;
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

  public function getPersistentCacheAdapter() {
    return $this->persistentCacheAdapter;
  }

  public function setPersistentCacheAdapter(AbstractAdapter $cacheAdapter) {
    $this->persistentCacheAdapter = $cacheAdapter;
    return $this;
  }

  public function getLastDocumentUpdateTime() {
    return $this->getPersistentCacheAdapter()
      ->getItem('last-document-update-time') ?: 0;
  }

  public function setLastDocumentUpdateTime($time) {
    $this->getPersistentCacheAdapter()
      ->setItem('last-document-update-time', $time);
    return $this;
  }

  public function getLastClientUpdateTime() {
    return $this->getPersistentCacheAdapter()
      ->getItem('last-client-update-time') ?: 0;
  }

  public function setLastClientUpdateTime($time) {
    $this->getPersistentCacheAdapter()
      ->setItem('last-client-update-time', $time);
    return $this;
  }

  public function updateDocuments($all = false) {
    return $this->fetchResources(
      '/documents',
      $all ? 0 : $this->getLastDocumentUpdateTime(),
      [$this, 'handleDocumentUpdate'],
      [$this, 'setLastDocumentUpdateTime']
    );
  }

  public function handleDocumentUpdate($document) {
    $this->getEventManager()->trigger('documentUpdated', $this, [
      'document' => $document,
    ]);
  }

  public function updateClients($all = false) {
    return $this->fetchResources(
      '/clients',
      $all ? 0 : $this->getLastClientUpdateTime(),
      [$this, 'handleClientUpdate'],
      [$this, 'setLastClientUpdateTime']
    );
  }

  public function handleClientUpdate($client) {
    $this->getEventManager()->trigger('clientUpdated', $this, [
      'client' => $client,
    ]);
  }

  protected function fetchResources(
    $collectionPath,
    $lastUpdateTime,
    callable $updateHandler,
    callable $lastUpdateTimeHandler = null
  ) {
    $count = self::PAGE_RESOURCE_COUNT;

    // do not fetch all resources at once, fetch them page by page
    do {
      // fetch page of resources
      $resources = $this->getBackboneService()->get(
        $collectionPath, [
          'updated_since' => $lastUpdateTime,
          'count' => $count,
        ]
      );

      // handle each updated resource
      foreach ($resources as $resource) {
        $updateHandler($resource);
        $lastUpdateTime = max($lastUpdateTime, $resource['updateTime']);
      }

      // track last update time
      if ($lastUpdateTimeHandler !== null) {
        $lastUpdateTimeHandler($lastUpdateTime);
      }

      // log
      trigger_error(sprintf(
        '%s - Fetched %d %s resources updated since %s',
        __METHOD__,
        count($resources),
        $collectionPath,
        date('Y-m-d H:i:s', $lastUpdateTime)
      ), E_USER_NOTICE);

    } while (count($resources) === $count);

    // successfully reached the end of the feed
    return true;
  }
}
