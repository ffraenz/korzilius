<?php

namespace Korzilius\Mapper;

use DateTime;
use Zend\Db\Sql;
use Zend\Db\Sql\Predicate;

use Korzilius\Entity\AbstractEntity as Entity;
use Korzilius\Entity\Client;

class ClientMapper extends AbstractEntityMapper {

  protected $table = 'client';
  protected $messageTable = 'message';

  protected function createObjectPrototype() {
    return new Client();
  }

  public function fetchLatest($count = 20, $offset = 0) {
    $select = $this->getSql()->select()
      ->join([
        $this->messageTable =>
          (new Sql\Select($this->messageTable))
            ->columns([
              'sender_client_id',
              'receiver_client_id',
              'active_time' => new Sql\Expression('MAX(send_time)'),
            ])
            ->where([
              'sender_client_id IS NOT NULL',
              'receiver_client_id IS NOT NULL',
            ], Sql\Predicate\PredicateSet::OP_OR)
            ->group(['sender_client_id', 'receiver_client_id'])
        ],
        sprintf(
          '%2$s.sender_client_id = %1$s.id OR ' .
          '%2$s.receiver_client_id = %1$s.id',
          $this->table,
          $this->messageTable),
        Sql\Select::SQL_STAR,
        Sql\Select::JOIN_LEFT
      )

      ->where([
        'id' => [
          new Sql\Expression('`sender_client_id`'),
          new Sql\Expression('`receiver_client_id`'),
        ]
      ])

      ->order('active_time DESC')
      ->order('update_time DESC')
      ->limit($count)
      ->offset($offset);

    return $this->populate(iterator_to_array($this->selectWith($select)));
  }

  public function fetchSingleByFacebookUserId($facebookUserId) {
    $select = $this->getSql()->select();
    $select->where->equalTo('facebook_user_id', $facebookUserId);
    return $this->populate($this->selectWith($select)->current() ?: null);
  }

  public function fetchAllByName($name, $firstname = null) {
    if (is_string($name) && $firstname !== null) {
      $name = sprintf('%s %s', $name, $firstname);
    }

    $select = $this->getSql()->select();

    $names = is_array($name) ? $name : [$name];
    foreach ($names as $name) {
      $select->where(
        (new Sql\Where())
          ->expression('CONCAT(`lastname`, " ", `firstname`) = ?', $name)
          ->or->expression('`company` = ?', $name)
      );
    }

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
