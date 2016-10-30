<?php

namespace Korzilius\Mapper;

use Korzilius\Entity\Message;

class MessageMapper extends AbstractEntityMapper {

  protected $table = 'message';

  protected function createObjectPrototype() {
    return new Message();
  }
}
