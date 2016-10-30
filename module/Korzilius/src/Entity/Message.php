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
}
