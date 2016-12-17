<?php

namespace Korzilius\Mapper;

use DateTime;
use Zend\Db\Sql;

use Korzilius\Entity\Message;
use Korzilius\Entity\Client;
use Korzilius\Mapper\UserMapper;

class MessageMapper extends AbstractEntityMapper {

  protected $table = 'message';

  protected $userMapper;

  public function getUserMapper() {
    return $this->userMapper;
  }

  public function setUserMapper(UserMapper $userMapper) {
    $this->userMapper = $userMapper;
    return $this;
  }

  protected function createObjectPrototype() {
    return new Message();
  }

  public function fetchSingleByExternalId($externalId) {
    $select = $this->getSql()->select();
    $select->where->equalTo('external_id', $externalId);
    return $this->populate($this->selectWith($select)->current() ?: null);
  }

  public function fetchAllByClient(
    Client $client,
    DateTime $sentBeforeTime = null,
    $count = 30
  ) {
    $select = $this->getSql()->select();

    $select->where
      ->nest()
        ->equalTo('sender_client_id', $client->getId())
        ->or
        ->equalTo('receiver_client_id', $client->getId())
      ->unnest();

    if ($sentBeforeTime !== null) {
      $select->where
        ->and
        ->lessThan('send_time', $sentBeforeTime->format('Y-m-d H:i:s'));
    }

    $select->order('send_time DESC');
    $select->limit($count);

    return $this->populate(iterator_to_array($this->selectWith($select)));
  }

  public function fetchAllByTargetId($targetId) {
    $select = $this->getSql()->select();
    $select->where->equalTo('target_id', $targetId);
    $select->order('send_time DESC');
    return $this->populate(iterator_to_array($this->selectWith($select)));
  }

  protected function populate($messages) {
    if ($messages === null) {
      return $messages;
    }
    if (!is_array($messages)) {
      return $this->populate([$messages]);
    }
    if (count($messages) === 0) {
      return $messages;
    }

    // collect needed user ids
    $userIds = [];
    foreach ($messages as $message) {
      array_push($userIds, $message->getReceiverUserId());
      array_push($userIds, $message->getSenderUserId());
    }

    // fetch embedded users
    $userIds = array_unique(array_filter($userIds));
    $users = count($userIds) > 0
      ? $this->getUserMapper()->fetchAllbyIds($userIds)
      : [];

    $usersMap = [];
    foreach ($users as $user) {
      $usersMap[$user->getId()] = $user;
    }

    // populate messages
    foreach ($messages as $message) {
      if ($message->getReceiverUserId()) {
        $message->setReceiverUser($usersMap[$message->getReceiverUserId()]);
      }
      if ($message->getSenderUserId()) {
        $message->setSenderUser($usersMap[$message->getSenderUserId()]);
      }
    }

    return $messages;
  }
}
