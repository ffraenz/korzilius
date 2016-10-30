<?php

namespace Korzilius\Entity;

use DateTime;

class Message extends AbstractEntity {

  protected $fieldTypeMap = [
    'id' => 'int',
    'externalId' => 'string',
    'type' => 'string',

    'senderClientId' => 'entityId',
    'senderClient' => 'entity',
    'senderUserId' => 'entityId',
    'senderUser' => 'entity',
    'receiverClientId' => 'entityId',
    'receiverClient' => 'entity',
    'receiverUserId' => 'entityId',
    'receiverUser' => 'entity',

    'text' => 'string',
    'targetId' => 'string',
    'meta' => 'keyValueArray',

    'createTime' => 'dateTime',
    'deliveredTime' => 'dateTime',
    'readTime' => 'dateTime',
  ];

  // identification
  protected $id;
  protected $externalId;
  protected $type;

  // sender and receiver
  protected $senderClientId;
  protected $senderClient;
  protected $senderUserId;
  protected $senderUser;
  protected $receiverClientId;
  protected $receiverClient;
  protected $receiverUserId;
  protected $receiverUser;

  // content
  protected $text;
  protected $targetId;
  protected $meta;

  // timestamps
  protected $createTime;
  protected $deliveredTime;
  protected $readTime;

  public function __construct() {
    $this->createTime = new DateTime();
  }
}
