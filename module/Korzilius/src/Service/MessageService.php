<?php

namespace Korzilius\Service;

use Korzilius\Mapper\MessageMapper;
use Korzilius\Entity\Message;

class MessageService {

  protected $messageMapper;

  public function getMessageMapper() {
    return $this->messageMapper;
  }

  public function setMessageMapper(MessageMapper $messageMapper) {
    $this->messageMapper = $messageMapper;
    return $this;
  }

  public function send(Message $message) {
    // store message
    $this->getMessageMapper()->save($message);

    // manipulate delivered / read timestamps

    // notify folks

  }
}
