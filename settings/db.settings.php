<?php

/**
 * @file
 * #polymer-generated: Automatically generated Drupal settings file.
 * polymer manages this file and may delete or overwrite the file unless this
 * comment is removed.  It is recommended that you leave this file alone.
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
