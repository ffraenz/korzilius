<?php

namespace KoBackbone;

return [
  'service_manager' => [
    'factories' => [
      Service\BackboneService::class => Factory\Service\BackboneService::class,
    ],
  ],
];
