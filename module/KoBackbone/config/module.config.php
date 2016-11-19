<?php

namespace KoBackbone;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Cache\Service\StorageCacheAbstractServiceFactory;

return [
  'service_manager' => [
    'factories' => [
      Service\BackboneService::class => Factory\Service\BackboneService::class,
      Service\JobService::class => Factory\Service\JobService::class,
    ],
    'abstract_factories' => [
      StorageCacheAbstractServiceFactory::class,
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\JobController::class => Factory\Controller\JobController::class,
    ],
  ],
  'router' => [
    'routes' => [
      'backbone/jobs/update' => [
        'type' => Segment::class,
        'options' => [
          'route' => '/jobs/backbone/update[/:resource]',
          'defaults' => [
            'controller' => Controller\JobController::class,
            'action' => 'update',
            'resource' => null,
          ],
        ],
      ],
    ],
  ],
  'caches' => [
    'korzilius-backbone-persistent' => [
      'adapter' => [
        'name' => 'filesystem',
      ],
      'options' => [
        'namespace' => 'backbone',
        'cache_dir' => 'data/cache/korzilius-backbone',
        'dir_level' => 0,
      ],
    ],
  ],
];
