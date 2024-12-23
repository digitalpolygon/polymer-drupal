<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Exception\AbortTasksException;

/**
 * Defines commands in the "drupal:settings" namespace.
 *
 * This class provides commands related to Drupal settings management.
 */
class SettingsCommands extends TaskBase
{
    /**
     * Path to the multisite settings.php file.
     *
     * @var string
     */
    private string $multiSiteSettingsFile;

    /**
     * Path to the multisite settings.db.php file.
     *
     * @var string
     */
    private string $dbSettingsFile;

    /**
     * Path to the template settings.db.php file.
     *
     * @var string
     */
    private string $dbSettingsFileTemplate;

    /**
     * Initializes paths for settings files based on the site name.
     *
     * @param string $site_name
     *   The name of the site.
     */
    private function initialize($site_name): void
    {
        /** @var string $docroot */
        $docroot = $this->getConfigValue('docroot');
        /** @var string $polymer_root */
        $polymer_root = $this->getConfigValue('polymer.root');

        $multisite_dir = "$docroot/sites/$site_name";
        $this->multiSiteSettingsFile = "$multisite_dir/settings.php";
        $this->dbSettingsFile =  "$multisite_dir/settings.db.php";
        $this->dbSettingsFileTemplate =  "$polymer_root/settings/settings.db.php";
    }

    /**
     * Initialize Drupal sites. Generates database settings into settings.db.php and
     * adds polymer.settings.php at the end of settings.php file for a Drupal site.
     *
     * @throws \Robo\Exception\AbortTasksException|\Robo\Exception\TaskException
     *   When unable to create or require settings files.
     */
    #[Command(name: 'drupal:init:settings')]
    public function generateDatabaseSettingsFiles(): void
    {
        /** @var array<string> $all_sites */
        $all_sites = $this->getConfigValue('drupal.multisites');
        foreach ($all_sites as $site) {
            $is_settings_exist = false;
            $this->switchSiteContext($site);

            // Initializes paths for settings files based on the site name.
            $this->initialize($site);

            // Check if 'settings.db.php' file already exists in target site.
            if (file_exists($this->dbSettingsFile)) {
                $this->say("Polymer settings database file already exists for $site site. Skipping.");
                $is_settings_exist = true;
            }

            // Proceed if settings not added for a site.
            if (!$is_settings_exist) {
                // Use the site name as the user/pass/name for the database.
                $db_name = $site;
                $db_user = $site;
                $db_pass = $site;

                if ($site !== 'default') {
                    // Ensure the database exists.
                    $this->createDatabaseIfNotExists($db_name, $db_user, $db_pass);
                }

                // Place 'settings.db.php' in multisite directory.
                $this->placeSettingsDatabaseFileOnMultiSite($db_name, $db_user, $db_pass);
                // Require the new 'settings.db.php' in multisite 'settings.php' file.
                $this->requireSettingsDatabaseFileOnMultiSite($site);
                // Adds polymer.settings.php at the end of settings.php in all Drupal sites.
                $this->generatePolymerSettingsFile($site);

                $this->say("Polymer settings added for $site site.");
            }
        }
    }

    /**
     * Requires 'settings.db.php' file in multisite settings.php file.
     *
     * @param string $site_name
     *   The name of the site.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   When unable to write or require settings file.
     */
    private function requireSettingsDatabaseFileOnMultiSite(string $site_name): void
    {
        $require_content = 'require __DIR__ . "/settings.db.php";' . "\n";

        /** @var \Robo\Task\File\Write $task */
        $task = $this->taskWriteToFile($this->multiSiteSettingsFile);
        $task->appendUnlessMatches('#settings.db.php#', $require_content);
        $task->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
        $task->append(true);
        $result = $task->run();
        if (!$result->wasSuccessful()) {
            throw new AbortTasksException("Unable to require the database settings files 'settings.db.php' into your multisite 'settings.php'.", $result->getExitCode());
        }
    }

    /**
     * Places settings.db.php file in multisite directory.
     *
     * @param string $db_name
     *   The name of the database.
     * @param string $db_user
     *   The database username.
     * @param string $db_pass
     *   The database password.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   When unable to write or require settings file.
     */
    private function placeSettingsDatabaseFileOnMultiSite(string $db_name, string $db_user, string $db_pass): void
    {
        // Read the default 'settings.db.php' template content.
        /** @var string $settings_db_content */
        $settings_db_content = file_get_contents($this->dbSettingsFileTemplate);
        /** @var \Robo\Task\File\Write $task */
        $task = $this->taskWriteToFile($this->dbSettingsFile);
        $task->text($settings_db_content);
        // Replace database credentials.
        $task->place('db_name', $db_name);
        $task->place('db_user', $db_user);
        $task->place('db_pass', $db_pass);
        $task->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
        $result = $task->run();
        if (!$result->wasSuccessful()) {
            throw new AbortTasksException("Unable to copy database settings file 'settings.db.php' into multisite directory.", $result->getExitCode());
        }
    }

    /**
     * Creates database and grants user permissions if not exists.
     *
     * @param string $db_name
     *   The name of the database.
     * @param string $db_user
     *   The database username.
     * @param string $db_pass
     *   The database password.
     *
     * @throws \Robo\Exception\AbortTasksException|\Robo\Exception\TaskException
     *   When unable to execute MySQL commands.
     */
    private function createDatabaseIfNotExists(string $db_name, string $db_user, string $db_pass): void
    {
        // Create the user
        $query = "CREATE USER '{$db_user}'@'%' IDENTIFIED BY '{$db_pass}';";
        $this->execMySqlQuery($query);
        // Create the database if not exists.
        $query = "CREATE DATABASE IF NOT EXISTS {$db_name};";
        $this->execMySqlQuery($query);
        # Granting User Permissions to the new db.
        $query = "GRANT ALL ON {$db_name}.* to '{$db_user}'@'%'; GRANT ALL ON {$db_name}.* to 'db'@'%';";
        $this->execMySqlQuery($query);
    }

    /**
     * Executes a MySQL query using 'ddev mysql' command.
     *
     * @todo: MVP support is for DDEV. During multisite creation, assume this runs in DDEV.
     *
     * @param string $query
     *   The MySQL query to execute.
     *
     * @return int
     *   The exit code of the command execution.
     *
     * @throws \Robo\Exception\AbortTasksException|\Robo\Exception\TaskException
     *   When unable to execute MySQL command.
     */
    private function execMySqlQuery(string $query): int
    {
        $command = 'mysql -uroot -proot -e "' . $query . '"';
        return $this->execCommand($command);
    }

    /**
     * Adds polymer.settings.php at the end of settings.php in a site.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   When unable to modify settings file.
     */
    public function generatePolymerSettingsFile(string $site_name): void
    {
        /** @var string $docroot */
        $docroot = $this->getConfigValue('docroot');
        $settings_file = "$docroot/sites/$site_name/settings.php";

        /** @var \Robo\Task\File\Write $task */
        $task = $this->taskWriteToFile($settings_file);
        $task->appendUnlessMatches('#vendor/digitalpolygon/polymer/settings/polymer.settings.php#', 'require DRUPAL_ROOT . "/../vendor/digitalpolygon/polymer/settings/polymer.settings.php";' . "\n");
        $task->append(true);
        $task->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
        $result = $task->run();

        if (!$result->wasSuccessful()) {
            throw new AbortTasksException("Unable to modify $settings_file.", $result->getExitCode());
        }
    }
}
