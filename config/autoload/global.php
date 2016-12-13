<?php

use Zend\Cache\Service\StorageCacheAbstractServiceFactory;
use Zend\Db\Adapter\AdapterServiceFactory;
use Zend\Db\Adapter\Adapter;

return [
  'service_manager' => [
    'abstract_factories' => [
      StorageCacheAbstractServiceFactory::class,
    ],
    'factories' => [
      Adapter::class => AdapterServiceFactory::class,
    ],
  ],
  'db' => [
    'driver' => 'Pdo',
    'dsn' => sprintf(
      'mysql:dbname=%s;host=%s',
      getenv('DB_NAME'),
      getenv('DB_HOST')
    ),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'driver_options' => [
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8mb4\''
    ],
  ],
  'korzilius' => [
    'websocket_service_endpoint' => getenv('KORZILIUS_WEBSOCKET_SERVICE_ENDPOINT'),
  ],
  'korzilius_backbone' => [
    'endpoint' => getenv('KORZILIUS_BACKBONE_ENDPOINT'),
    'apikey' => getenv('KORZILIUS_BACKBONE_APIKEY'),
    'job_access_token' => getenv('JOB_ACCESS_TOKEN'),
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
