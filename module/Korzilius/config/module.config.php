<?php

namespace Korzilius;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
  'service_manager' => [
    'factories' => [
      Service\MessageService::class => Factory\Service\MessageService::class,
      Mapper\MessageMapper::class => InvokableFactory::class,
      Mapper\ClientMapper::class => InvokableFactory::class,
      Entity\EntityArrayHydrator::class => InvokableFactory::class,
      Entity\EntityDbHydrator::class => InvokableFactory::class,
    ],
    'initializers' => [
      Initializer\DbAdapterInitializer::class,
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\IndexController::class => Factory\Controller\IndexController::class,
      Controller\ClientResourceController::class => Factory\Controller\ClientResourceController::class,
    ],
  ],
  'view_helpers' => [
    'invokables' => [
      'component' => View\Helper\Component::class,
    ],
  ],
  'router' => [
    'routes' => [
      'home' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/',
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'index',
          ],
        ],
      ],
      'api' => [
        'type' => Literal::class,
        'may_terminate' => false,
        'options' => [
          'route' => '/api',
        ],
        'child_routes' => [
          'clients' => [
            'type' => Segment::class,
            'options' => [
              'route' => '/clients[/:id]',
              'constraints' => [
                'id' => '[0-9]+',
              ],
              'defaults' => [
                'controller' => Controller\ClientResourceController::class,
              ],
            ],
          ],
        ],
      ],
    ],
  ],
  'view_manager' => [
    'strategies' => [
      'ViewJsonStrategy',
    ],
    'display_not_found_reason' => true,
    'display_exceptions' => true,
    'doctype' => 'HTML5',
    'not_found_template' => 'error/404',
    'exception_template' => 'error/index',
    'template_path_stack' => [
        __DIR__ . '/../view',
    ],
  ],
];
