<?php

namespace KoFacebook\Service;

use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Json;

use KoFacebook\Exception\WebhookServiceException;
use KoFacebook\Entity\WebhookUpdate;

class WebhookService {

  protected $appSecret;
  protected $webhookVerifyToken;

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

    // prepare response to send the challenge back
    $response->setStatusCode(200);
    $response->setContent($challenge);

    // no update in this webhook call, return null
    return null;
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

    // prepare empty ok response
    $response->setStatusCode(200);
    $response->setContent('');

    // return webhook update
    return new WebhookUpdate($json);
  }
}
