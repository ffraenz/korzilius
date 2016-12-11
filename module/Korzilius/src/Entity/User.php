<?php

namespace Korzilius\Entity;

use DateTime;

class User extends AbstractEntity {

  protected $fields = [
    'id' => [
      'type' => 'int',
    ],
    'name' => [
      'type' => 'string',
    ],
    'active' => [
      'type' => 'boolean',
    ],
    'email' => [
      'type' => 'string',
    ],
    'eloUserId' => [
      'type' => 'int',
    ],
    'eloScanShareName' => [
      'type' => 'string',
    ],
  ];
}
