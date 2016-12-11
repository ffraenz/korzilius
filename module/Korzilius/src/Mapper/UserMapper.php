<?php

namespace Korzilius\Mapper;

use Korzilius\Entity\User;

class UserMapper extends AbstractEntityMapper {

  protected $table = 'user';

  protected function createObjectPrototype() {
    return new User();
  }

  public function fetchAll() {
    $select = $this->getSql()->select();
    return $this->populate(iterator_to_array($this->selectWith($select)));
  }
}
