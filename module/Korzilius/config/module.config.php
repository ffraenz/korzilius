<?php

namespace Korzilius;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
  'service_manager' => [
    'factories' => [
      Service\MessageService::class => Factory\Service\MessageService::class,
      Mapper\MessageMapper::class => Factory\Mapper\MessageMapper::class,
      Mapper\ClientMapper::class => InvokableFactory::class,
      Mapper\UserMapper::class => InvokableFactory::class,
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
      Controller\MessageResourceController::class => Factory\Controller\MessageResourceController::class,
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
        'type' => Segment::class,
        'options' => [
          'route' => '/[clients/:client_id]',
          'priority' => -10,
          'constraints' => [
            'client_id' => '[0-9]+',
          ],
          'defaults' => [
            'controller' => Controller\IndexController::class,
            'action' => 'index',
          ],
        ],
      ],
      'api' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/api',
        ],
        'child_routes' => [
          'clients' => [
            'type' => Segment::class,
            'may_terminate' => true,
            'options' => [
              'route' => '/clients[/:client_id]',
              'constraints' => [
                'client_id' => '[0-9]+',
              ],
              'defaults' => [
                'controller' => Controller\ClientResourceController::class,
              ],
            ],
            'child_routes' => [
              'messages' => [
                'type' => Segment::class,
                'options' => [
                  'route' => '/messages[/]',
                  'defaults' => [
                    'controller' => Controller\MessageResourceController::class,
                  ],
                ],
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
