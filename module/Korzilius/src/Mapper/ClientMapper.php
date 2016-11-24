<?php

namespace Korzilius\Mapper;

use DateTime;

use Korzilius\Entity\AbstractEntity as Entity;
use Korzilius\Entity\Client;

class ClientMapper extends AbstractEntityMapper {

  protected $table = 'client';

  protected function createObjectPrototype() {
    return new Client();
  }

  public function fetchLatest($count = 20, $offset = 0) {
    $select = $this->getSql()->select();
    $select->order('update_time DESC');
    $select->limit($count);
    $select->offset($offset);
    return $this->populate(iterator_to_array($this->selectWith($select)));
  }

  public function save(Entity $entity, $exists = false) {
    // check if entity has an id
    if ($entity->getId() === null) {
      throw new Exception(sprintf(
        '%s - Client entities must have an id set before saving.',
        __METHOD__
      ));
    }

    // update timestamps
    $entity->setUpdateTime(new DateTime());
    if ($entity->getCreateTime() === null) {
      $entity->setCreateTime(new DateTime());
    }

    // extract data
    $data = $this->getHydrator()->extract($entity);

    // check if entity with this id exists
    if (!$exists && $this->fetchSingleById($entity->getId()) === null) {
      $this->getTableGateway()->insert($data);
    } else {
      $this->getTableGateway()->update($data, [ 'id' => $entity->getId() ]);
    }

    return $this;
  }
}
