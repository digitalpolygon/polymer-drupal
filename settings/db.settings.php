<?php

/**
 * @file
 * This file contains database related configuration. Use this file to store
 * predictable settings based on the environment.
 */

if (getenv('IS_DDEV_PROJECT') == 'true') {
  $ddevSettingsFile = DRUPAL_ROOT . "/$site_path/settings.ddev.php";
  if (!file_exists($ddevSettingsFile)) {
    $databases['default']['default'] = [
      'database' => $site_name,
      'username' => 'db',
      'password' => 'db',
      'prefix' => '',
      'host' => 'db',
      'port' => 3306,
      'isolation_level' => 'READ COMMITTED',
      'driver' => 'mysql',
      'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
      'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
    ];
  }
}
