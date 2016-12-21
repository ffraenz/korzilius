<?php

namespace Korzilius\Entity;

use DateTime;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;

class EntityArrayHydrator implements HydratorInterface {

  public function extract($entity) {
    if ($entity === null) {
      return null;
    }

    $fields = $entity->getFields();
    $data = [];

    // iterate through each entity field
    foreach ($fields as $name => $field) {
      // respect ignore extract attribute
      if (
        !isset($field['ignoreExtract']) ||
        array_search(__CLASS__, $field['ignoreExtract']) === false
      ) {
        $type = $field['type'];
        $value = $entity->{'get' . ucfirst($name)}();

        // extract value
        if ($value !== null) {
          $extractionMethod = 'extract' . ucfirst($type);
          if (method_exists($this, $extractionMethod)) {
            $value = $this->{$extractionMethod}($value);
          }
        }

        // add value to array
        $data[$name] = $value;
      }
    }
    return $data;
  }

  protected function extractDateTime($dateTime) {
    return $dateTime->getTimestamp();
  }

  protected function extractEntity($entity) {
    return $this->extract($entity);
  }

  public function hydrate(array $data, $entity) {
    $fields = $entity->getFields();

    foreach ($data as $name => $value) {
      // respect ignore hydrate attribute
      if (
        !isset($field['ignoreHydrate']) ||
        array_search(__CLASS__, $field['ignoreHydrate']) === false
      ) {
        // has the entity a field that matches this key
        if (isset($fields[$name])) {

          // hydrate value
          if ($value !== null) {
            $hydrationMethod = 'hydrate' . ucfirst($fields[$name]['type']);
            if (method_exists($this, $hydrationMethod)) {
              $value = $this->{$hydrationMethod}($value);
            }
          }

          // apply value
          $method = 'set' . ucfirst($name);
          $entity->{$method}($value);
        }
      }
    }
    return $entity;
  }

  protected function hydrateDateTime($timestamp) {
    $dateTime = new DateTime();
    $dateTime->setTimestamp($timestamp);
    return $dateTime;
  }
}
