<?php

namespace KoFacebook;

use Zend\Router\Http\Literal;

return [
  'service_manager' => [
    'factories' => [
      Service\WebhookService::class => Factory\Service\WebhookService::class,
    ],
  ],
  'controllers' => [
    'factories' => [
      Controller\WebhookController::class => Factory\Controller\WebhookController::class,
    ],
  ],
  'view_manager' => [
    'strategies' => [
      'ViewJsonStrategy',
    ],
  ],
  'router' => [
    'routes' => [
      'webhook' => [
        'type' => Literal::class,
        'options' => [
          'route' => '/webhooks',
          'may_terminate' => false,
        ],
        'child_routes' => [
          'facebook' => [
            'type' => Literal::class,
            'options' => [
              'route' => '/facebook',
              'defaults' => [
                'controller' => Controller\WebhookController::class,
                'action' => 'index',
              ],
            ],
          ],
        ],
      ],
    ],
  ],
];
