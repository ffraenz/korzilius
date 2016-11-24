<?php

namespace Korzilius\Mapper;

use DateTime;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Select;

use Korzilius\Entity\AbstractEntity as Entity;
use Korzilius\Entity\EntityDbHydrator;

abstract class AbstractEntityMapper implements AdapterAwareInterface {

  protected $table;

  protected $dbAdapter;
  protected $tableGateway;
  protected $hydrator;

  public function getDbAdapter() {
    return $this->dbAdapter;
  }

  public function setDbAdapter(Adapter $dbAdapter) {
    $this->dbAdapter = $dbAdapter;
    return $this;
  }

  protected function getSql() {
    return $this->getTableGateway()->getSql();
  }

  protected function selectWith(Select $select) {
    return $this->getTableGateway()->selectWith($select);
  }

  protected function createHydrator() {
    return new EntityDbHydrator();
  }

  protected function getHydrator() {
    if ($this->hydrator === null) {
      $this->hydrator = $this->createHydrator();
    }
    return $this->hydrator;
  }

  abstract protected function createObjectPrototype();

  protected function getTableGateway() {
    if ($this->tableGateway === null) {
      $objectPrototype = $this->createObjectPrototype();

      // create result set prototype
      $resultSetPrototype = new HydratingResultSet(
        $this->getHydrator(), $objectPrototype);

      // create table gateway
      $this->tableGateway = new TableGateway(
          $this->table, $this->getDbAdapter(), null, $resultSetPrototype);
    }
    return $this->tableGateway;
  }

  public function fetchSingleById($id) {
    $select = $this->getSql()->select();
    $select->where->equalTo('id', $id);
    return $this->populate($this->selectWith($select)->current() ?: null);
  }

  public function fetchAllByIds(array $ids) {
    $select = $this->getSql()->select();
    $select->where->in('id', $ids);
    return $this->populate(iterator_to_array($this->selectWith($select)));
  }

  protected function populate($entities) {
    // nothing to populate
    return $entities;
  }

  public function save(Entity $entity) {
    // update timestamps
    $entity->setUpdateTime(new DateTime());
    if ($entity->getCreateTime() === null) {
      $entity->setCreateTime(new DateTime());
    }

    // extract data
    $data = $this->getHydrator()->extract($entity);

    if ($entity->getId() === null) {
      // insert entity
      $this->getTableGateway()->insert($data);
      $entity->setId($this->getTableGateway()->lastInsertValue);
    } else {
      // check if entity with this id exists
      if ($this->fetchSingleById($entity->getId()) === null) {
        throw new Exception(sprintf(
          '%s - Entity with id "%s" does not exist.',
          __METHOD__,
          $entity->getId()
        ));
      }

      // update entity
      $this->getTableGateway()->update($data, [ 'id' => $entity->getId() ]);
    }
    
    return $this;
  }

  public function delete(Entity $entity) {
    if ($entity->getId() !== null) {
      $this->getTableGateway()->delete([ 'id' => $entity->getId() ]);
      $entity->setId(null);
    }
    return $this;
  }
}
