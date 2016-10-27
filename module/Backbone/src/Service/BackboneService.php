<?php

namespace Backbone\Service;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Json\Json;

class BackboneService {

  protected $httpClient;

  protected $backboneEndpoint;
  protected $backboneApikey;

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
    $this->backboneEndpoint = $config['backbone']['endpoint'];
    $this->backboneApikey = $config['backbone']['apikey'];
    return $this;
  }

  protected function getBackboneEndpoint() {
    return $this->backboneEndpoint;
  }

  protected function getBackboneApikey() {
    return $this->backboneApikey;
  }

  public function requestResource(
    $path, $method = Request::METHOD_GET, $parameters = []
  ) {
    // prepare request
    $request = new Request();
    $request->setMethod($method);

    // compose url
    $url = $this->getBackboneEndpoint() . $path;
    $request->setUri($url);

    // add api key to parameters
    $parameters['apikey'] = $this->getBackboneApikey();

    // add parameters to request
    if ($method === Request::METHOD_GET) {
      $request->getQuery()->fromArray($parameters);
    } else {
      $request->getPost()->fromArray($parameters);
    }

    // set headers
    $headers = $request->getHeaders();
    $headers->addHeaders([
      'Accept' => 'application/json',
      'User-Agent' => 'Korzilius/0.0.1',
    ]);

    // retrieve response from backbone
    $response = $this->getHttpClient()->send($request);

    if ($response->isNotFound()) {
      // return null if resource not found
      return null;
    }

    // decode json data
    $json = $response->getBody();
    $data = Json::decode($json, Json::TYPE_ARRAY);

    return $data;
  }

  public function get($path, $parameters = []) {
    return $this->requestResource($path, Request::METHOD_GET, $parameters);
  }

  public function create($path, $parameters = []) {
    return $this->requestResource($path, Request::METHOD_POST, $parameters);
  }

  public function update($path, $parameters = []) {
    return $this->requestResource($path, Request::METHOD_PUT, $parameters);
  }

  public function delete($path, $parameters = []) {
    return $this->requestResource($path, Request::METHOD_DELETE, $parameters);
  }
}
