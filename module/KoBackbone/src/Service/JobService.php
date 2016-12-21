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
      ->getItem('last-document-update-time');
  }

  public function setLastDocumentUpdateTime($time) {
    $this->getPersistentCacheAdapter()
      ->setItem('last-document-update-time', $time);
    return $this;
  }

  public function getLastClientUpdateTime() {
    return $this->getPersistentCacheAdapter()
      ->getItem('last-client-update-time');
  }

  public function setLastClientUpdateTime($time) {
    $this->getPersistentCacheAdapter()
      ->setItem('last-client-update-time', $time);
    return $this;
  }

  public function updateDocuments($all = false) {
    $updateTime = new DateTime();
    $parameters = [];

    if (!$all) {
      $lastUpdateTime = $this->getLastDocumentUpdateTime();
      if ($lastUpdateTime !== null) {
        $parameters['updated_since'] = $lastUpdateTime;
      }
    }

    $lastUpdateTime = $this->fetchResources('/documents', $parameters,
      function($document) use ($all) {
        $this->getEventManager()->trigger('documentUpdated', $this, [
          'document' => $document,
          'flush' => $all,
        ]);
      });

    // update last document update time
    if ($lastUpdateTime !== null) {
      $this->setLastDocumentUpdateTime($lastUpdateTime);
    }

    return $this;
  }

  public function updateClients($all = false) {
    $updateTime = new DateTime();
    $parameters = [];

    if (!$all) {
      $lastUpdateTime = $this->getLastClientUpdateTime();
      if ($lastUpdateTime !== null) {
        $parameters['updated_since'] = $lastUpdateTime;
      }
    }

    $lastUpdateTime = $this->fetchResources('/clients', $parameters,
      function($client) use ($all) {
        $this->getEventManager()->trigger('clientUpdated', $this, [
          'client' => $client,
          'flush' => $all,
        ]);
      });

    // update last client update time
    if ($lastUpdateTime !== null) {
      $this->setLastClientUpdateTime($lastUpdateTime);
    }

    return $this;
  }

  protected function fetchResources(
    $collectionPath, array $parameters, callable $handler
  ) {
    $count = self::PAGE_RESOURCE_COUNT;
    $offset = 0;

    // track last update time
    $lastUpdateTime = 0;
    if (isset($parameters['update_time'])) {
      $lastUpdateTime = $parameters['update_time'];
    }

    // do not fetch all resources at once, fetch them page by page
    do {
      // fetch page of resources
      $resources = $this->getBackboneService()->get(
        $collectionPath,
        array_merge($parameters, [
          'offset' => $offset,
          'count' => $count,
        ])
      );

      // handle each updated resource
      foreach ($resources as $resource) {
        $handler($resource);

        $lastUpdateTime =
          max($lastUpdateTime, $resource['updateTime']);
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

    // return last update time
    return ($lastUpdateTime > 0 ? $lastUpdateTime : null);
  }
}
