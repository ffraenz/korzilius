<?php

namespace Korzilius\Controller;

use DateTime;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Hydrator\HydratorInterface;

use Korzilius\Mapper\MessageMapper;
use Korzilius\Mapper\ClientMapper;

class MessageResourceController extends AbstractRestfulController {

  protected $messageMapper;
  protected $clientMapper;
  protected $hydrator;

  protected $identifierName = 'message_id';

  public function getMessageMapper() {
    return $this->messageMapper;
  }

  public function setMessageMapper(MessageMapper $messageMapper) {
    $this->messageMapper = $messageMapper;
    return $this;
  }

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
    $clientId = $this->params()->fromRoute('client_id');

    $client = $this->getClientMapper()->fetchSingleById($clientId);
    if ($client === null) {
      $this->getResponse()->setStatusCode(404);
      return new JsonModel();
    }

    $sentBeforeTimestamp = $this->params()->fromQuery('sent_before', null);

    $sentBeforeTime = null;
    if ($sentBeforeTimestamp !== null) {
      $sentBeforeTime = new DateTime();
      $sentBeforeTime->setTimestamp($sentBeforeTimestamp);
    }

    $messages = $this->getMessageMapper()->fetchAllByClient(
      $client, $sentBeforeTime);

    $data = array_map(function($message) {
      return $this->getHydrator()->extract($message);
    }, $messages);

    return new JsonModel($data);
  }
}
