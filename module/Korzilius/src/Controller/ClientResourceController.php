<?php

namespace Korzilius\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Hydrator\HydratorInterface;

use Korzilius\Mapper\ClientMapper;

class ClientResourceController extends AbstractRestfulController {

  protected $clientMapper;
  protected $hydrator;

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
    $clients = $this->getClientMapper()->fetchLatest(10);

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
