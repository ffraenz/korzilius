<?php

namespace Korzilius\Controller;

use DateTime;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Hydrator\HydratorInterface;

use Korzilius\Mapper\ClientMapper;

class ClientResourceController extends AbstractRestfulController {

  protected $clientMapper;
  protected $hydrator;

  protected $identifierName = 'client_id';

  public function getClientMapper() {
    return $this->clientMapper;
  }

  public function setClientMapper(ClientMapper $clientMapper) {
    $this->clientMapper = $clientMapper;
    return $this;
  }

  public function getHydrator() {
    return $this->hydrator;
  }

  public function setHydrator(HydratorInterface $hydrator) {
    $this->hydrator = $hydrator;
    return $this;
  }

  public function getList() {
    $query = $this->params()->fromQuery('q', null);
    $activeBeforeTimestamp = $this->params()->fromQuery('active_before', null);

    $activeBeforeTime = null;
    if ($activeBeforeTimestamp !== null) {
      $activeBeforeTime = new DateTime();
      $activeBeforeTime->setTimestamp($activeBeforeTimestamp);
    }

    if ($query !== null) {
      $clients = $this->getClientMapper()->fetchAllByKeywords($query);
    } else {
      $clients = $this->getClientMapper()->fetchLatest($activeBeforeTime);
    }

    $data = array_map(function($client) {
      return $this->getHydrator()->extract($client);
    }, $clients);

    return new JsonModel($data);
  }

  public function get($id) {
    $client = $this->getClientMapper()->fetchSingleById($id);

    if ($client === null) {
      $this->getResponse()->setStatusCode(404);
      return new JsonModel();
    }

    $data = $this->getHydrator()->extract($client);
    return new JsonModel($data);
  }
}
