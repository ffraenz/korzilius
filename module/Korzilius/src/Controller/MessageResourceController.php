<?php

namespace Korzilius\Controller;

use DateTime;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Hydrator\HydratorInterface;

use Korzilius\Exception\MessageServiceException;
use Korzilius\Service\MessageService;
use Korzilius\Mapper\MessageMapper;
use Korzilius\Mapper\ClientMapper;
use Korzilius\Entity\Message;

class MessageResourceController extends AbstractRestfulController {

  protected $messageMapper;
  protected $messageService;
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

  public function getMessageService() {
    return $this->messageService;
  }

  public function setMessageService(MessageService $messageService) {
    $this->messageService = $messageService;
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

  public function create($data) {
    $clientId = $this->params()->fromRoute('client_id');

    $client = $this->getClientMapper()->fetchSingleById($clientId);
    if ($client === null) {
      $this->getResponse()->setStatusCode(404);
      return new JsonModel();
    }

    $text = $data['text'];
    $channel = $data['channel'];

    $receiver = $client;
    $sender = null;

    try {
      $message = $this->getMessageService()
        ->postToChannel($channel, $receiver, $text, $sender);

    } catch (MessageServiceException $exception) {
      // log error
      trigger_error(sprintf(
        '%s - MessageServiceException thrown: %s',
        __METHOD__,
        $exception->getMessage()
      ), E_USER_WARNING);

      // respond
      $response->setStatusCode($exception->getCode());
      return new JsonModel([
        'error' => [
          'message' => $exception->getMessage(),
          'code' => $exception->getCode(),
        ],
      ]);
    }

    $data = $this->getHydrator()->extract($message);
    return new JsonModel($data);
  }
}
