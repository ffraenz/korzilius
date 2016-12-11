<?php

namespace Korzilius\Mapper;

use Zend\Db\Sql;

use Korzilius\Entity\Message;
use Korzilius\Entity\Client;

class MessageMapper extends AbstractEntityMapper {

  protected $table = 'message';

  protected function createObjectPrototype() {
    return new Message();
  }

  public function fetchAllByClient(Client $client, $count = 30, $offset = 0) {
    $select = $this->getSql()->select();
    $select->where([
      'sender_client_id' => $client->getId(),
      'receiver_client_id' => $client->getId(),
    ], Sql\Predicate\PredicateSet::OP_OR);

    $select->order('send_time DESC');
    $select->limit($count);
    $select->offset($offset);
    return $this->populate(iterator_to_array($this->selectWith($select)));
  }

  public function fetchAllByTargetId($targetId) {
    $select = $this->getSql()->select();
    $select->where->equalTo('target_id', $targetId);
    $select->order('send_time DESC');
    return $this->populate(iterator_to_array($this->selectWith($select)));
  }
}
