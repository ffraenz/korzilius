<?php
return [
  'service_manager' => [
    'abstract_factories' => [
      'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
    ],
  ],
  'backbone' => [
    'endpoint' => getenv('KORZILIUS_BACKBONE_ENDPOINT'),
    'apikey' => getenv('KORZILIUS_BACKBONE_APIKEY'),
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
