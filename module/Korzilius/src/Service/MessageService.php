<?php

namespace Korzilius\Service;

use DateTime;
use Zend\Hydrator\HydratorInterface;

use KoFacebook\Service\GraphService;
use KoFacebook\Exception\GraphServiceException;
use Korzilius\Exception\MessageServiceException;
use Korzilius\Mapper\MessageMapper;
use Korzilius\Entity\Client;
use Korzilius\Entity\User;
use Korzilius\Entity\Message;

class MessageService {

  protected $messageMapper;
  protected $hydrator;

  protected $webSocketService;
  protected $graphService;

  public function getMessageMapper() {
    return $this->messageMapper;
  }

  public function setMessageMapper(MessageMapper $messageMapper) {
    $this->messageMapper = $messageMapper;
    return $this;
  }

  public function getHydrator() {
    return $this->hydrator;
  }

  public function setHydrator(HydratorInterface $hydrator) {
    $this->hydrator = $hydrator;
    return $this;
  }

  public function getWebSocketService() {
    return $this->webSocketService;
  }

  public function setWebSocketService(WebSocketService $webSocketService) {
    $this->webSocketService = $webSocketService;
    return $this;
  }

  public function getGraphService() {
    return $this->graphService;
  }

  public function setGraphService(GraphService $graphService) {
    $this->graphService = $graphService;
    return $this;
  }

  public function postToChannel($channel, $receiver, $text, $sender = null) {
    // prepare message object
    $message = (new Message())
      ->setReceiver($receiver)
      ->setSender($sender)
      ->setSendTime(new DateTime())
      ->setText($text);

    // post message on given channel
    if ($channel === 'intern') {
      $message
        ->setType('intern')
        ->setDeliveredTime(new DateTime());
    } else if ($channel === 'facebook') {
      $message->setType('facebook');

      // verify receiver
      if (
        !$receiver instanceof Client ||
        $receiver->getFacebookUserId() === null
      ) {
        throw new MessageServiceException(
          "Can't post message to facebook: The receiver needs to be " .
          "a Client with known facebook user id.",
          400);
      }

      try {
        // post message to facebook
        $response = $this->getGraphService()
          ->createMessage($receiver->getFacebookUserId(), [
            'text' => $text,
          ]);

      } catch (GraphServiceException $exception) {
        throw new MessageServiceException(sprintf(
          "Can't post message to facebook: %s",
          $exception->getMessage()), 400);
      }

      // store external id
      if (isset($response['message_id'])) {
        $message->setExternalId($response['message_id']);
      }

      // message has been delivered successfully
      $message->setDeliveredTime(new DateTime());

    } else {
      throw new MessageServiceException(sprintf(
        "Can't post message to unrecognized channel '%s'.",
        $channel), 400);
    }

    return $this->post($message);
  }

  public function post(Message $message) {
    // is new message or message update
    $messageUpdate = ($message->getId() !== null);

    // save message
    $this->getMessageMapper()->save($message);

    // notify app clients
    $messageData = $this->getHydrator()->extract($message);

    if ($messageUpdate) {
      $this->getWebSocketService()
        ->pushEvent('messageUpdated', $messageData);
    } else {
      $this->getWebSocketService()
        ->pushEvent('messageReceived', $messageData);
    }

    return $message;
  }

  public function remove(Message $message) {
    // delete existing message
    $this->getMessageMapper()->delete($message);

    // notify app clients
    $messageData = $this->getHydrator()->extract($message);
    $this->getWebSocketService()
      ->pushEvent('messageDeleted', $messageData);

    return $message;
  }
}
