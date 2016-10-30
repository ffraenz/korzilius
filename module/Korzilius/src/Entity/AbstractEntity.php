<?php

namespace Korzilius\Entity;

use Exception;
use DateTime;

abstract class AbstractEntity {

  protected $fieldTypeMap = [
    'id' => 'int',
  ];

  protected $id;

  public function __call($name, $args) {
    $getOrSet = substr($name, 0, 3);

    if (!in_array($getOrSet, ['get', 'set'])) {
      throw new Exception(sprintf(
        '%s - Invalid method "%s".',
        __METHOD__,
        $name));
    }

    $field = lcfirst(substr($name, 3));

    if (!isset($this->fieldTypeMap[$field])) {
      throw new Exception(sprintf(
        '%s - Unknown field "%s".',
        __METHOD__,
        $field));
    }

    $fieldType = $this->fieldTypeMap[$field];
    $method = $getOrSet . ucfirst($fieldType) . 'Field';

    if (!method_exists($this, $method)) {
      // blank getter / setter
      if ($getOrSet === 'get') {
        return $this->{$field};
      } else {
        $this->{$field} = $args[0];
        return $this;
      }
    }

    // call type specific getter / setter
    array_unshift($args, $field);
    return call_user_func_array([$this, $method], $args);
  }

  protected function setIntField($field, $value) {
    if ($value !== null && !is_numeric($value)) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type integer.',
        __METHOD__,
        $field));
    }
    $this->{$field} = ($value !== null ? intval($value) : null);
    return $this;
  }

  protected function setStringField($field, $value) {
    if ($value !== null && !is_string($value)) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type string.',
        __METHOD__,
        $field));
    }
    $this->{$field} = $value;
    return $this;
  }

  protected function setDateTimeField($field, $value) {
    if ($value !== null && !$value instanceOf DateTime) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type DateTime.',
        __METHOD__,
        $field));
    }
    $this->{$field} = $value;
    return $this;
  }

  public function getKeyValueArrayField($field, $key = null, $default = null) {
    if ($this->{$field} === null) {
      return $default;
    }

    if ($key === null) {
      return $this->{$field};
    }

    if (isset($this->{$field}[$key])) {
      return $this->{$field}[$key];
    }

    return $default;
  }

  public function setKeyValueArrayField(
    $field, $keyOrArray = null, $value = null
  ) {
    if (is_string($keyOrArray)) {
      // create empty array if it does not exist
      if ($this->{$field} === null) {
        $this->{$field} = [];
      }
      $this->{$field}[$keyOrArray] = $value;
    }

    if ($keyOrArray !== null && !is_array($keyOrArray)) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type array.',
        __METHOD__,
        $field));
    }

    $this->{$field} = $keyOrArray;
    return $this;
  }

  protected function setEntityField($field, $value) {
    if ($value !== null && !$value instanceOf AbstractEntity) {
      throw new Exception(sprintf(
        '%s - Field "%s" expects a value of type %s.',
        __METHOD__,
        $field,
        __CLASS__));
    }

    // set entity id
    if ($value !== null) {
      $this->{$field . 'Id'} = $value->getId();
    } else {
      $this->{$field . 'Id'} = null;
    }

    // set entity
    $this->{$field} = $value;
    return $this;
  }

  protected function setEntityIdField($field, $value) {
    if ($this->{$field} !== $value) {
      $this->setIntField($field, $value);
      // field name without 'Id'
      $objectField = substr(0, -2);
      // clear object
      $this->{$objectField} = null;
    }
    return $this;
  }
}
