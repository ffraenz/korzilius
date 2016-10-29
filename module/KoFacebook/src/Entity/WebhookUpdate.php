<?php

namespace KoFacebook\Entity;

use DateTime;
use Zend\Json\Json;

class WebhookUpdate {

  protected $objectType;
  protected $change;

  public function __construct($json) {
    $data = Json::decode($json, Json::TYPE_ARRAY);
    $this->objectType = $data['object'];
    $this->change = $data['entry'];
  }

  public function getObjectType() {
    return $this->objectType;
  }

  public function getChangeData() {
    return $this->change;
  }

  public function getObjectId() {
    return $this->change['id'];
  }

  public function getTime() {
    $time = new DateTime();
    $time->setTimestamp($this->change['time']);
    return $time;
  }
}
