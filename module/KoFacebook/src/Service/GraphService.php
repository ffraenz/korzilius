<?php

namespace KoFacebook\Service;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request;
use Zend\Json\Json;

use KoFacebook\Exception\GraphException;

class GraphService {

  protected $httpClient;

  protected $graphApiEndpoint;
  protected $appSecret;
  protected $pageAccessToken;

  const SENDER_ACTION_MARK_SEEN = 'mark_seen';
  const SENDER_ACTION_TYPING_ON = 'typing_on';
  const SENDER_ACTION_TYPING_OFF = 'typing_off';

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
    $this->appSecret = $facebookConfig['app_secret'];
    $this->pageAccessToken = $facebookConfig['page_access_token'];
    return $this;
  }

  public function getGraphApiEndpoint() {
    return $this->graphApiEndpoint;
  }

  public function getAppSecret() {
    return $this->appSecret;
  }

  public function getPageAccessToken() {
    return $this->pageAccessToken;
  }

  public function requestResource(
    $path, $method = Request::METHOD_GET, $parameters = [], $data = null
  ) {
    // prepare request
    $request = new Request();
    $request->setMethod($method);

    // compose url
    $url = $this->getGraphApiEndpoint() . $path;
    $request->setUri($url);

    // set headers
    $headers = $request->getHeaders();
    $headers->addHeaders([
      'Accept' => 'application/json',
      'User-Agent' => 'Korzilius/0.0.1',
    ]);

    // add app secret proof to request if
    // the access token parameter is included
    if (isset($parameters['access_token'])) {
      $parameters['appsecret_proof'] =
        hash_hmac('sha256', $parameters['access_token'], $this->getAppSecret());
    }

    // add parameters
    $request->getQuery()->fromArray($parameters);

    // add data
    if ($data !== null && $method !== Request::METHOD_GET) {
      $headers->addHeaderLine('Content-Type', 'application/json');
      $request->setContent(Json::encode($data));
    }

    // retrieve response from facebook
    $response = $this->getHttpClient()->send($request);

    // decode json data
    $responseJson = $response->getBody();
    $responseData = Json::decode($responseJson, Json::TYPE_ARRAY);
    trigger_error($responseJson);

    // check for errors
    if (isset($responseData['error'])) {
      // throw exception
      $message = $responseData['error']['message'];
      $code = $responseData['error']['code'];
      throw new GraphException($message, $code);
    }

    return $responseData;
  }

  public function get($path, $parameters = []) {
    return $this->requestResource($path, Request::METHOD_GET, $parameters);
  }

  public function create($path, $parameters = [], $data = null) {
    return $this->requestResource(
      $path, Request::METHOD_POST, $parameters, $data);
  }

  public function update($path, $parameters = [], $data = null) {
    return $this->requestResource(
      $path, Request::METHOD_PUT, $parameters, $data);
  }

  public function delete($path, $parameters = [], $data = null) {
    return $this->requestResource(
      $path, Request::METHOD_DELETE, $parameters, $data);
  }

  public function createMessage($recipietUserId, $message) {
    $this->create('/me/messages', [
      'access_token' => $this->getPageAccessToken(),
    ], [
      'recipient' => [ 'id' => $recipietUserId ],
      'message' => $message,
    ]);
  }

  public function createMessageSenderAction($recipietUserId, $senderAction) {
    $this->create('/me/messages', [
      'access_token' => $this->getPageAccessToken(),
    ], [
      'recipient' => [ 'id' => $recipietUserId ],
      'sender_action' => $senderAction,
    ]);
  }
}
