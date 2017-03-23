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

  protected function joinActiveTime($select) {
    // retrieve active time field using a left join on the messages table
    return $select->join([
      'activeTimeJoin' => (new Sql\Select($this->messageTable))
          ->columns([
            'client_id' => new Sql\Expression(
              'COALESCE(sender_client_id, receiver_client_id)'),
            'active_time' => new Sql\Expression('MAX(send_time)'),
          ])
          ->group('client_id')
          ->having('client_id IS NOT NULL')
      ],
      'client_id = id',
      ['active_time'],
      Sql\Select::JOIN_LEFT
    );
  }

  public function fetchLatest(
    DateTime $activeBeforeTime = null,
    $count = 30
  ) {
    $select = $this->getSql()->select();
    $this->joinActiveTime($select);

    if ($activeBeforeTime !== null) {
      $select->where
        ->and
        ->lessThan('active_time', $activeBeforeTime->format('Y-m-d H:i:s'));
    }

    $select
      ->order('active_time DESC')
      ->order('update_time DESC')
      ->limit($count);

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

  public function fetchAllByAddress($street, $postCode) {
    $select = $this->getSql()->select();
    $select->where->expression(
      'CONCAT(`house_number`, ", ", `street`) = ?', $street);
    $select->where->equalTo('post_code', $postCode);
    return $this->populate(iterator_to_array($this->selectWith($select)));
  }

  public function fetchAllByKeywords($rawKeywords) {
    $select = $this->getSql()->select();

    $keywords = '';
    foreach (explode(' ', $rawKeywords) as $keyword) {
      // prevent empty keywords
      $keyword = trim($keyword);
      if ($keyword !== '') {
        $keywords .= '+' . $keyword . '* ';
      }
    }

    $select->columns([
      Sql\Select::SQL_STAR,
      'name_score' => new Sql\Expression(
        'MATCH (company, firstname, lastname) ' .
        'AGAINST (? in boolean mode)',
        $keywords),
    ]);

    $select->having->greaterThan('name_score', 2.0);
    $select->order('name_score', 'DESC');

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
