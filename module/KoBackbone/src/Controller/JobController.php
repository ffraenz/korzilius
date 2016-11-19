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

  public function updateAction() {
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

    $knownResources = ['documents', 'clients'];
    $updateResources = [];

    $resource = $this->params()->fromRoute('resource');
    if ($resource === null) {
      // update all resources
      $updateResources = $knownResources;
    } else if (array_search($resource, $knownResources, true) !== false) {
      // update given resource
      array_push($updateResources, $resource);
    } else {
      // unknown resource
      $response->setStatusCode(404);
      return new JsonModel([
        'error' => [
          'message' => 'Unknown resource',
          'code' => 404,
        ],
      ]);
    }

    $all = ($request->getQuery('all') === '1');

    // increase max execution time to 5 minutes
    ini_set('max_execution_time', 300);

    // run jobs
    foreach ($updateResources as $resource) {
      switch ($resource) {
        case 'documents':
          $this->getJobService()->updateDocuments($all);
          break;

        case 'clients':
          $this->getJobService()->updateClients($all);
          break;
      }
    }

    return new JsonModel([
      'success' => true,
    ]);
  }
}
