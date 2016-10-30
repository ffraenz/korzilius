<?php

namespace Korzilius\Entity;

use Exception;
use DateTime;

abstract class AbstractEntity {

  protected $fields = [
    'id' => [
      'type' => 'int',
    ],
    'createTime' => [
      'type' => 'dateTime',
    ],
    'updateTime' => [
      'type' => 'dateTime',
    ],
  ];

  public function __call($name, $args) {
    $getOrSet = substr($name, 0, 3);

    if (!in_array($getOrSet, ['get', 'set'])) {
      throw new Exception(sprintf(
        '%s - Invalid method "%s".',
        __METHOD__,
        $name));
    }

    $name = lcfirst(substr($name, 3));

    if (!isset($this->fields[$name])) {
      throw new Exception(sprintf(
        '%s - Unknown field "%s".',
        __METHOD__,
        $name));
    }

    $field = $this->fields[$name];
    $method = $getOrSet . ucfirst($field['type']) . 'Field';

    if (!method_exists($this, $method)) {
      // use blank getter and setter
      if ($getOrSet === 'get') {
        return $this->getField($name);
      } else {
        return $this->setField($name, $args[0]);
      }
    }

    // call type specific getter / setter
    array_unshift($args, $name);
    return call_user_func_array([$this, $method], $args);
  }

  public function getFields() {
    return $this->fields;
  }

  public function getField($name) {
    $field = $this->fields[$name];
    if (!isset($field['value'])) {
      return null;
    }
    return $field['value'];
  }

  public function setField($name, $value) {
    $this->fields[$name]['value'] = $value;
    return $this;
  }

  protected function setIntField($name, $value) {
    if ($value !== null && !is_numeric($value)) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type integer.',
        __METHOD__,
        $name));
    }
    return $this->setField($name, $value !== null ? intval($value) : null);
  }

  protected function setStringField($name, $value) {
    if ($value !== null && !is_string($value)) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type string.',
        __METHOD__,
        $name));
    }
    return $this->setField($name, $value);
  }

  protected function setDateTimeField($name, $value) {
    if ($value !== null && !$value instanceOf DateTime) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type DateTime.',
        __METHOD__,
        $name));
    }
    return $this->setField($name, $value);
  }

  public function getKeyValueArrayField($name, $key = null, $default = null) {
    if ($key === null) {
      return $this->getField($name);
    }
    if ($this->getField($name) === null) {
      return $default;
    }
    if (isset($this->fields[$name]['value'][$key])) {
      return $this->fields[$name]['value'][$key];
    }
    return $default;
  }

  public function setKeyValueArrayField(
    $name, $keyOrArray = null, $value = null
  ) {
    if (is_string($keyOrArray)) {
      // create empty array if it does not exist
      if ($this->getField($name) === null) {
        $this->fields[$name]['value'] = [];
      }
      // set single array entry
      $this->fields[$name]['value'][$keyOrArray] = $value;
      return $this;
    }

    if ($keyOrArray !== null && !is_array($keyOrArray)) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type array.',
        __METHOD__,
        $field));
    }

    return $this->setField($name, $keyOrArray);
  }

  protected function setEntityField($name, $value) {
    if ($value !== null && !$value instanceOf AbstractEntity) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type %s.',
        __METHOD__,
        $name,
        __CLASS__));
    }

    // set entity id
    if ($value !== null) {
      $this->setField($name . 'Id', $value->getId());
    } else {
      $this->setField($name . 'Id', null);
    }

    // set entity
    return $this->setField($name, $value);
  }

  protected function setEntityIdField($name, $value) {
    if ($this->getField($name) !== $value) {
      $this->setIntField($name, $value);
      // field name without 'Id'
      $objectFieldName = substr($name, 0, -2);
      // clear object
      $this->setField($objectFieldName, null);
    }
    return $this;
  }
}
