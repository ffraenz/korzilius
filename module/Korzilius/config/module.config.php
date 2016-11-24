<?php

namespace Korzilius;

use Zend\Router\Http\Literal;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
  'service_manager' => [
    'factories' => [
      Service\MessageService::class => Factory\Service\MessageService::class,
      Mapper\MessageMapper::class => InvokableFactory::class,
    ],
    'initializers' => [
      Initializer\DbAdapterInitializer::class,
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\IndexController::class => Factory\Controller\IndexController::class,
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
    ],
  ],
  'view_manager' => [
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
