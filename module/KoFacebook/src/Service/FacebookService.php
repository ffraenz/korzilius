<?php

namespace KoFacebook\Service;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Json\Json;

class FacebookService {

  protected $httpClient;

  protected $graphApiEndpoint;

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
    $facebookConfig = $config['korzilius_facebook'];
    $this->graphApiEndpoint = $facebookConfig['graph_api_endpoint'];
    return $this;
  }

  public function getGraphApiEndpoint() {
    return $this->graphApiEndpoint;
  }

  public function requestResource(
    $path, $method = Request::METHOD_GET, $parameters = []
  ) {
    // prepare request
    $request = new Request();
    $request->setMethod($method);

    // compose url
    $url = $this->getGraphApiEndpoint() . $path;
    $request->setUri($url);

    // add parameters to request
    if ($method === Request::METHOD_GET) {
      $request->getQuery()->fromArray($parameters);
    } else {
      $request->setContent(Json::encode($parameters));
      $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
    }

    // set headers
    $request->getHeaders()->addHeaders([
      'Accept' => 'application/json',
      'User-Agent' => 'Korzilius/0.0.1',
    ]);

    // retrieve response from facebook
    $response = $this->getHttpClient()->send($request);

    // decode json data
    $json = $response->getBody();
    trigger_error($json);
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
