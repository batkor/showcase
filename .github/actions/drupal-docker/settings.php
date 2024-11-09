<?php

include __DIR__ . '/default.settings.php';

$databases['default']['default'] = [
  'database' => 'sites/default/files/.sqlite',
  'prefix' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\sqlite',
  'driver' => 'sqlite',
];
