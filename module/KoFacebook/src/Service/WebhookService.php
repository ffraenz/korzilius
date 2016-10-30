<?php

namespace KoFacebook\Service;

use DateTime;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Json;

use KoFacebook\Exception\WebhookServiceException;

class WebhookService implements EventManagerAwareInterface {

  protected $events;

  protected $appSecret;
  protected $webhookVerifyToken;

  public function getEventManager() {
    if ($this->events === null) {
      $this->setEventManager(new EventManager());
    }
    return $this->events;
  }

  public function setEventManager(EventManagerInterface $events) {
    $events->setIdentifiers([__CLASS__, get_called_class()]);
    $this->events = $events;
    return $this;
  }

  public function configure(array $config) {
    $facebookConfig = $config['korzilius_facebook'];
    $this->appSecret = $facebookConfig['app_secret'];
    $this->webhookVerifyToken = $facebookConfig['webhook_verify_token'];
    return $this;
  }

  public function getAppSecret() {
    return $this->appSecret;
  }

  public function getWebhookVerifyToken() {
    return $this->webhookVerifyToken;
  }

  public function handleRequest(Request $request, Response $response) {
    // update requests are made via post method
    if ($request->getMethod() === Request::METHOD_POST) {
      return $this->handleUpdateRequest($request, $response);
    }

    // verification requests are made via get method
    if ($request->getMethod() === Request::METHOD_GET) {
      return $this->handleVerificationRequest($request, $response);
    }

    // unexpected method, throw exception
    throw new WebhookServiceException('Unexpected method.', 400);
  }

  public function handleVerificationRequest(
    Request $request, Response $response
  ) {
    $mode = $request->getQuery('hub_mode');
    $challenge = $request->getQuery('hub_challenge');
    $verifyToken = $request->getQuery('hub_verify_token');

    // check verify token
    if ($verifyToken !== $this->getWebhookVerifyToken()) {
      throw new WebhookServiceException('Unexpected verify token.', 401);
    }

    // check mode
    if ($mode !== 'subscribe') {
      throw new WebhookServiceException(
        sprintf('Unexpected mode "%s".', $mode), 400);
    }

    // send the challenge back to complete verification
    $response->setStatusCode(200);
    $response->setContent($challenge);
    return $response;
  }

  public function handleUpdateRequest(Request $request, Response $response) {
    // check for signature header
    if (!$request->getHeaders()->has('X-Hub-Signature')) {
      throw new WebhookServiceException(
        'Missing header "X-Hub-Signature".', 401);
    }

    // read signature and algorithm
    $signature = $request->getHeader('X-Hub-Signature')->getFieldValue();
    list($algo, $signature) = array_pad(explode('=', $signature), 2, null);

    // check algorithm
    if (!in_array($algo, ['sha1'])) {
      throw new WebhookServiceException(
        sprintf('Unexpected signature algorithm "%s".', $algo), 401);
    }

    // sign content with app secret
    $json = $request->getContent();
    $expectedSignature = hash_hmac($algo, $json, $this->getAppSecret());

    // verify signature
    if ($signature !== $expectedSignature) {
      throw new WebhookServiceException('Unexpected signature.', 401);
    }

    // create webhook update object
    $data = Json::decode($json, Json::TYPE_ARRAY);
    $this->handleUpdate($data);

    // respond with an empty ok response
    $response->setStatusCode(200);
    $response->setContent('');
    return $response;
  }

  public function handleUpdate($data) {
    $objectType = $data['object'];
    $entries = $data['entry'];

    // iterate over update entries
    foreach ($entries as $entry) {
      if (isset($entry['messaging'])) {
        // this is an update from the messenger platform
        // iterate over messaging entries
        foreach ($entry['messaging'] as $messagingEntry) {
          if (isset($messagingEntry['message'])) {
            $this->handleMessageReceivedUpdate($messagingEntry);
          } else if (isset($messagingEntry['delivery'])) {
            $this->handleMessageDeliveredUpdate($messagingEntry);
          } else if (isset($messagingEntry['read'])) {
            $this->handleMessageReadUpdate($messagingEntry);
          }
        }
      }
    }
  }

  public function handleMessageReceivedUpdate($data) {
    $time = new DateTime();
    $time->setTimestamp(intval($data['timestamp'] / 1000));

    $attachments = [];
    if (isset($data['message']['attachments'])) {
      $attachments = $data['message']['attachments'];
    }

    $this->getEventManager()->trigger('messageReceived', $this, [
      'userId' => $data['sender']['id'],
      'pageId' => $data['recipient']['id'],
      'messageId' => $data['message']['mid'],
      'time' => $time,
      'text' => $data['message']['text'],
      'attachments' => $attachments,
    ]);
  }

  public function handleMessageDeliveredUpdate($data) {
    $watermark = new DateTime();
    $watermark->setTimestamp(intval($data['delivery']['watermark'] / 1000));

    $this->getEventManager()->trigger('messageDelivered', $this, [
      'userId' => $data['sender']['id'],
      'pageId' => $data['recipient']['id'],
      'watermark' => $watermark,
    ]);
  }

  public function handleMessageReadUpdate($data) {
    $watermark = new DateTime();
    $watermark->setTimestamp(intval($data['read']['watermark'] / 1000));

    $this->getEventManager()->trigger('messageRead', $this, [
      'userId' => $data['sender']['id'],
      'pageId' => $data['recipient']['id'],
      'watermark' => $watermark,
    ]);
  }
}
