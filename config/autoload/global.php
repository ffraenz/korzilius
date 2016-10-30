<?php
return [
  'service_manager' => [
    'abstract_factories' => [
      'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
    ],
  ],
  'korzilius_backbone' => [
    'endpoint' => getenv('KORZILIUS_BACKBONE_ENDPOINT'),
    'apikey' => getenv('KORZILIUS_BACKBONE_APIKEY'),
  ],
  'korzilius_facebook' => [
    'graph_api_endpoint' => 'https://graph.facebook.com/v2.8',
    'app_id' => getenv('FACEBOOK_APP_ID'),
    'app_secret' => getenv('FACEBOOK_APP_SECRET'),
    'webhook_verify_token' => getenv('FACEBOOK_WEBHOOK_VERIFY_TOKEN'),
    'page_access_token' => getenv('FACEBOOK_PAGE_ACCESS_TOKEN'),
  ],
  'caches' => [
    'memcached' => [
      'adapter' => [
        'name' =>'memcached',
        'lifetime' => 7200,
        'options'  => [
          'servers' => [
            [ getenv('MEMCACHED_HOST'), getenv('MEMCACHED_PORT') ],
          ],
          'namespace'  => getenv('MEMCACHED_NAMESPACE'),
        ],
      ],
    ],
  ],
];
