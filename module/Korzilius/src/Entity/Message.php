<?php

namespace Korzilius\Entity;

use DateTime;

class Message extends AbstractEntity {

  protected $fields = [
    // identification
    'id' => [
      'type' => 'int',
    ],
    'externalId' => [
      'type' => 'string',
    ],
    'type' => [
      'type' => 'string',
    ],

    // sender
    'senderClientId' => [
      'type' => 'entityId',
    ],
    'senderClient' => [
      'type' => 'entity',
    ],
    'senderUserId' => [
      'type' => 'entityId',
    ],
    'senderUser' => [
      'type' => 'entity',
    ],

    // receiver
    'receiverClientId' => [
      'type' => 'entityId',
    ],
    'receiverClient' => [
      'type' => 'entity',
    ],
    'receiverUserId' => [
      'type' => 'entityId',
    ],
    'receiverUser' => [
      'type' => 'entity',
    ],

    // content
    'text' => [
      'type' => 'string',
    ],
    'targetId' => [
      'type' => 'string',
    ],
    'meta' => [
      'type' => 'keyValueArray',
    ],

    // timestamps
    'sendTime' => [
      'type' => 'dateTime',
    ],
    'deliveredTime' => [
      'type' => 'dateTime',
    ],
    'readTime' => [
      'type' => 'dateTime',
    ],
    'createTime' => [
      'type' => 'dateTime',
    ],
    'updateTime' => [
      'type' => 'dateTime',
    ],
  ];

  public function setSender($sender = null) {
    if ($sender instanceof User) {
      $this->setSenderUser($sender);
    } else if ($sender instanceof Client) {
      $this->setSenderClient($sender);
    } else if ($sender !== null) {
      throw new Exception('Sender is expected to be a Client or User', 500);
    }
    return $this;
  }

  public function setReceiver($receiver = null) {
    if ($receiver instanceof User) {
      $this->setReceiverUser($receiver);
    } else if ($receiver instanceof Client) {
      $this->setReceiverClient($receiver);
    } else if ($receiver !== null) {
      throw new Exception('Receiver is expected to be a Client or User', 500);
    }
    return $this;
  }
}
