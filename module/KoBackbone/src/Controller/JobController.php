<?php

namespace KoBackbone\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

use KoBackbone\Service\JobService;

class JobController extends AbstractActionController {

  protected $jobService;
  protected $jobAccessToken;

  public function getJobService() {
    return $this->jobService;
  }

  public function setJobService(JobService $jobService) {
    $this->jobService = $jobService;
    return $this;
  }

  public function configure(array $config) {
    $backboneConfig = $config['korzilius_backbone'];
    $this->jobAccessToken = $backboneConfig['job_access_token'];
    return $this;
  }

  public function getJobAccessToken() {
    return $this->jobAccessToken;
  }

  public function updateDocumentsAction() {
    $request = $this->getRequest();
    $response = $this->getResponse();

    // verify access token
    $accessToken = $request->getQuery('token');
    if (empty($accessToken) || $accessToken !== $this->getJobAccessToken()) {
      $response->setStatusCode(403);
      return new JsonModel([
        'error' => [
          'message' => 'Forbidden',
          'code' => 403,
        ],
      ]);
    }

    $all = ($request->getQuery('all') === '1');
    $this->getJobService()->updateDocuments($all);

    return new JsonModel([
      'success' => true,
    ]);
  }

  public function updateClientsAction() {
    $request = $this->getRequest();
    $response = $this->getResponse();

    // verify access token
    $accessToken = $request->getQuery('token');
    if (empty($accessToken) || $accessToken !== $this->getJobAccessToken()) {
      $response->setStatusCode(403);
      return new JsonModel([
        'error' => [
          'message' => 'Forbidden',
          'code' => 403,
        ],
      ]);
    }

    $all = ($request->getQuery('all') === '1');
    $this->getJobService()->updateClients($all);

    return new JsonModel([
      'success' => true,
    ]);
  }
}
