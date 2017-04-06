<?php

namespace Korzilius\Entity;

use DateTime;

class Client extends AbstractEntity {

  protected $fields = [
    'id' => [
      'type' => 'int',
    ],
    'active' => [
      'type' => 'boolean',
    ],
    'laluxClientId' => [
      'type' => 'string',
    ],
    'facebookUserId' => [
      'type' => 'int',
    ],
    'title' => [
      'type' => 'string',
    ],
    'company' => [
      'type' => 'string',
    ],
    'firstname' => [
      'type' => 'string',
    ],
    'lastname' => [
      'type' => 'string',
    ],
    'street' => [
      'type' => 'string',
    ],
    'houseNumber' => [
      'type' => 'string',
    ],
    'postCode' => [
      'type' => 'string',
    ],
    'location' => [
      'type' => 'string',
    ],
    'country' => [
      'type' => 'string',
    ],
    'emailPrivate' => [
      'type' => 'string',
    ],
    'emailPro' => [
      'type' => 'string',
    ],
    'phonePrivate' => [
      'type' => 'string',
    ],
    'phonePro' => [
      'type' => 'string',
    ],
    'mobilePrivate' => [
      'type' => 'string',
    ],
    'mobilePro' => [
      'type' => 'string',
    ],
    'fax' => [
      'type' => 'string',
    ],
    'birthdate' => [
      'type' => 'dateTime',
    ],
    'vat' => [
      'type' => 'string',
    ],
    'language' => [
      'type' => 'string',
    ],
    'iban' => [
      'type' => 'string',
    ],
    'note' => [
      'type' => 'string',
    ],
    'syncTime' => [
      'type' => 'dateTime',
    ],
    'createTime' => [
      'type' => 'dateTime',
    ],
    'updateTime' => [
      'type' => 'dateTime',
    ],
    'activeTime' => [
      'type' => 'dateTime',
      'ignoreExtract' => [
        EntityDbHydrator::class,
      ],
    ],
  ];

  public function getNameLabel() {
    return !empty($this->getCompany())
      ? $this->getCompany()
      : sprintf(
          '%s %s',
          $this->getLastname(),
          $this->getFirstname());
  }

  public function getFullNameLabel() {
    return !empty($this->getCompany())
      ? sprintf(
          '%s / %s %s %s',
          $this->getCompany(),
          $this->getTitle(),
          $this->getLastname(),
          $this->getFirstname())
      : sprintf(
          '%s %s %s',
          $this->getTitle(),
          $this->getLastname(),
          $this->getFirstname());
  }

  public function getStreetLabel() {
    $street = implode(', ', array_filter([
      $this->getHouseNumber(),
      $this->getStreet(),
    ]));
    return !empty($street) ? $street : null;
  }

  public function getLocationLabel() {
    $location = implode(' ', array_filter([
      strtoupper($this->getCountry()),
      $this->getPostCode(),
      $this->getLocation(),
    ]));
    return !empty($location) ? $location : null;
  }
}
