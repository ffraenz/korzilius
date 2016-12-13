<?php

namespace Korzilius\Service;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Json\Json;

class WebSocketService {

  protected $httpClient;

  protected $endpoint;

  protected function getHttpClient() {
    if ($this->httpClient === null) {
      $this->httpClient = new HttpClient();
      $this->httpClient->setOptions([
        'adapter' => 'Zend\Http\Client\Adapter\Curl',
        'timeout' => 10,
      ]);
    }
    return $this->httpClient;
  }

  public function configure(array $config) {
    $this->endpoint = $config['korzilius']['websocket_service_endpoint'];
    return $this;
  }

  protected function getEndpoint() {
    return $this->endpoint;
  }

  public function pushEvent($name, array $data = []) {
    // compose web socket service request
    $request = new Request();
    $request->setMethod('POST');
    $request->setUri($this->getEndpoint() . '/events');

    $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
    $request->setContent(Json::encode([
      'name' => $name,
      'data' => $data,
    ]));

    $this->getHttpClient()->send($request);
  }
}
