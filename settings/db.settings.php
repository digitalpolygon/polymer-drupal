<?php

/**
 * @file
 * This file contains database related configuration. Use this file to store
 * predictable settings based on the environment.
 */

if (getenv('IS_DDEV_PROJECT') == 'true') {
    if (isset($site_path)) {
        $ddev_settings_file = DRUPAL_ROOT . "/$site_path/settings.ddev.php";
        if (file_exists($ddev_settings_file)) {
            require $ddev_settings_file;
        }
    }
    if (isset($site_name)) {
        $database_to_use = match($site_name) {
            'default' => 'db',
            default => $site_name,
        };
        $databases['default']['default'] = [
            'database' => $database_to_use,
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
