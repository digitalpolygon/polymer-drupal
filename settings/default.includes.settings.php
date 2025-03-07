<?php

/**
 * @file
 * Generated by Polymer. A central aggregation point for adding settings files.
 */

/**
 * Adds any additional settings files required by your application.
 *
 * To use, copy and rename this file to be `includes.settings.php and
 * add file references to the `additionalSettingsFiles` array below.
 *
 * Files required into this file are included into the polymer.settings.php file
 * prior to the inclusion of the local.settings.php file, so that that file may
 * override any variable set up the chain. Taken in total, the points at which
 * files are included into the base settings.php file are...
 *
 * settings.php
 *   |
 *   ---- polymer.settings.php
 *          |
 *          ---- includes.settings.php
 *          |       |
 *          |       ---- foo.settings.php
 *          |       ---- bar.settings.php
 *          |       ---- ....
 *          |
 *          ---- settings.local.php
 *
 * If you want to add settings to every site defined in the codebase, you can do
 * so using the default.global.settings.php file in web/sites/settings.
 */

/**
 * Add settings using full file location and name.
 *
 * It is recommended that you use the DRUPAL_ROOT and $site_dir components to
 * provide full paths in a dynamic manner.
 */
$additionalSettingsFiles = [
    // e.g,( DRUPAL_ROOT . "/sites/$site_dir/settings/foo.settings.php" )
];

foreach ($additionalSettingsFiles as $settingsFile) {
    if (file_exists($settingsFile)) {
        // phpcs:ignore
        require $settingsFile;
    }
}
