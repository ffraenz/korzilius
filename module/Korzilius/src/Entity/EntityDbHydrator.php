<?php

namespace Korzilius\Entity;

use DateTime;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use Zend\Json\Json;

class EntityDbHydrator implements HydratorInterface {

  protected static $underscoreNamingStrategy;

  protected function getUnderscoreNamingStrategy() {
    if (self::$underscoreNamingStrategy instanceof UnderscoreNamingStrategy) {
      return self::$underscoreNamingStrategy;
    }
    return static::$underscoreNamingStrategy = new UnderscoreNamingStrategy();
  }

  public function extract($entity) {
    $fields = $entity->getFields();
    $data = [];
    foreach ($fields as $name => $field) {
      // respect ignore extract attribute
      if (
        !isset($field['ignoreExtract']) ||
        array_search(__CLASS__, $field['ignoreExtract']) === false
      ) {
        $fieldType = $field['type'];
        if ($fieldType !== 'entity') {
          $dbName = self::getUnderscoreNamingStrategy()->extract($name);
          $value = $entity->{'get' . ucfirst($name)}();
          $dbValue = $this->extractType($fieldType, $value);
          $data[$dbName] = $dbValue;
        }
      }
    }
    return $data;
  }

  protected function extractType($type, $value) {
    if ($value === null) {
      return null;
    }
    $method = 'extract' . ucfirst($type);
    if (!method_exists($this, $method)) {
      // use raw value
      return $value;
    }
    return $this->{$method}($value);
  }

  protected function extractDateTime($dateTime) {
    return $dateTime->format('Y-m-d H:i:s');
  }

  protected function extractBoolean($boolean) {
    return $boolean ? 1 : 0;
  }

  protected function extractKeyValueArray($array) {
    return Json::encode($array);
  }

  public function hydrate(array $data, $entity) {
    $fields = $entity->getFields();
    foreach ($fields as $name => $field) {
      // respect ignore hydrate attribute
      if (
        !isset($field['ignoreHydrate']) ||
        array_search(__CLASS__, $field['ignoreHydrate']) === false
      ) {
        $dbName = self::getUnderscoreNamingStrategy()->extract($name);
        if (isset($data[$dbName])) {
          $value = $this->hydrateType($field['type'], $data[$dbName]);
          $method = 'set' . ucfirst($name);
          $entity->{$method}($value);
        }
      }
    }
    return $entity;
  }

  protected function hydrateType($type, $value) {
    if ($value === null) {
      return null;
    }
    $method = 'hydrate' . ucfirst($type);
    if (!method_exists($this, $method)) {
      // use raw value
      return $value;
    }
    return $this->{$method}($value);
  }

  protected function hydrateDateTime($value) {
    return new DateTime($value);
  }

  protected function hydrateBoolean($value) {
    return $value;
  }

  protected function hydrateKeyValueArray($value) {
    return Json::decode($value, Json::TYPE_ARRAY);
  }
}
