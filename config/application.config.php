<?php
return [
  'modules' => [
    'Zend\Router',
    'Zend\Validator',
    'Application',
  ],
  'module_listener_options' => [
    'module_paths' => [
      './module',
      './vendor',
    ],
    'config_glob_paths' => [
      realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php',
    ],
  ],
];
