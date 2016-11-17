<?php

namespace KoBackbone;

use Zend\Router\Http\Literal;

return [
  'service_manager' => [
    'factories' => [
      Service\BackboneService::class => Factory\Service\BackboneService::class,
      Service\JobService::class => Factory\Service\JobService::class,
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\JobController::class => Factory\Controller\JobController::class,
    ],
  ],
  'router' => [
    'routes' => [
      'backbone/jobs/update-documents' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/jobs/backbone/update-documents',
          'defaults' => [
            'controller' => Controller\JobController::class,
            'action' => 'update-documents',
          ],
        ],
      ],
      'backbone/jobs/update-clients' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/jobs/backbone/update-clients',
          'defaults' => [
            'controller' => Controller\JobController::class,
            'action' => 'update-clients',
          ],
        ],
      ],
    ],
  ],
];
