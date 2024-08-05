<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Argument;
use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\Usage;
use Robo\Exception\AbortTasksException;
use Robo\Tasks;

/**
 * Provides commands for upgrading Drupal core.
 *
 * This class defines commands to automate the upgrade process for Drupal core,
 * including version updates, database updates, cache clearing, and configuration export.
 */
class DrupalUpgradeCommands extends Tasks
{
    /**
     * Upgrades Drupal core based on the provided options.
     *
     * This method manages the full upgrade process:
     * - Initializes the environment.
     * - Executes the selected upgrade strategy.
     * - Applies database updates.
     * - Clears the cache.
     * - Exports configuration.
     *
     * @param string|null $new_version
     *   The specific version to upgrade to. If not provided, upgrades to the latest stable version.
     * @param bool $latest_minor
     *   If TRUE, upgrades to the latest stable minor version.
     * @param bool $latest_major
     *   If TRUE, upgrades to the latest stable major version.
     * @param bool $next_major
     *   If TRUE, upgrades to the next major version.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if any step in the upgrade process fails.
     */
    #[Command(name: 'drupal:upgrade')]
    #[Argument(name: 'new_version', description: 'The new version to upgrade Drupal core to.')]
    #[Argument(name: 'latest_minor', description: 'Upgrade to the latest stable minor version.')]
    #[Argument(name: 'latest_major', description: 'Upgrade to the latest stable major version.')]
    #[Argument(name: 'next_major', description: 'Upgrade to the next major version.')]
    #[Usage(name: 'drupal:upgrade', description: 'Executes the Drupal upgrade process.')]
    #[Usage(name: 'drupal:upgrade --new_version=10.2.3', description: 'Upgrades Drupal core to version 10.2.3.')]
    #[Usage(name: 'drupal:upgrade --latest_minor', description: 'Upgrades Drupal core to the latest stable minor version.')]
    #[Usage(name: 'drupal:upgrade --latest_major', description: 'Upgrades Drupal core to the latest stable major version.')]
    #[Usage(name: 'drupal:upgrade --next_major', description: 'Upgrades Drupal core to the latest stable of the next major version.')]
    public function upgradeDrupal(string $new_version = null, bool $latest_minor = false, bool $latest_major = false, bool $next_major = false): void
    {
        $this->initialize();
        $this->doUpgradeDrupal($new_version, $latest_minor, $latest_major, $next_major);
        $this->applyDrupalDatabaseUpdates();
        $this->clearDrupalCache();
        $this->exportDrupalConfiguration();
        $this->say("Drupal upgrade process completed successfully.");
    }

    /**
     * Initializes the environment for the upgrade process.
     *
     * This method should include any setup or configuration needed
     * before performing the upgrade. It is currently a placeholder.
     */
    private function initialize(): void
    {
        // @todo: Implement initialization logic.
    }

    /**
     * Determines and executes the appropriate Drupal upgrade strategy.
     *
     * @param string|null $new_version
     *   The specific version to upgrade to.
     * @param bool $latest_minor
     *   If TRUE, upgrades to the latest stable minor version.
     * @param bool $latest_major
     *   If TRUE, upgrades to the latest stable major version.
     * @param bool $next_major
     *   If TRUE, upgrades to the next major version.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if no upgrade option is selected.
     */
    private function doUpgradeDrupal(?string $new_version = null, bool $latest_minor = false, bool $latest_major = false, bool $next_major = false): void
    {
        if ($new_version) {
            $this->upgradeDrupalToVersion($new_version);
            return;
        }
        if ($latest_minor) {
            $this->upgradeDrupalToLatestMinor();
            return;
        }
        if ($latest_major) {
            $this->upgradeDrupalToLatestMajor();
            return;
        }
        if ($next_major) {
            $this->upgradeDrupalToNextMajor();
            return;
        }
        // Throw an AbortTasksException if no upgrade option is selected.
        throw new AbortTasksException("No upgrade option selected. Please specify a version or upgrade type.");
    }

    /**
     * Exports Drupal configuration using Drush.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the configuration export fails.
     */
    private function exportDrupalConfiguration(): void
    {
        $this->say("Exporting configuration...");
        $task = $this->taskExec('ddev exec -- drush cex -y');
        $result = $task->run();
        $success = $result->wasSuccessful();
        if (!$success) {
            throw new AbortTasksException("Error exporting Drupal configuration.");
        }
    }

    /**
     * Clears Drupal cache using Drush.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the cache clearing fails.
     */
    private function clearDrupalCache(): void
    {
        $this->say("Clearing cache...");
        $task = $this->taskExec('ddev exec -- drush cr');
        $result = $task->run();
        $success = $result->wasSuccessful();
        if (!$success) {
            throw new AbortTasksException("Error clearing Drupal cache.");
        }
    }

    /**
     * Applies database updates using Drush.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if applying database updates fails.
     */
    private function applyDrupalDatabaseUpdates(): void
    {
        $this->say("Applying database updates...");
        $task = $this->taskExec('ddev exec -- drush updb -y');
        $result = $task->run();
        $success = $result->wasSuccessful();
        if (!$success) {
            throw new AbortTasksException("Error applying database updates.");
        }
    }

    /**
     * Upgrades Drupal core to a specific version.
     *
     * @param string $new_version
     *   The new Drupal version to upgrade to.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the upgrade to the specified version fails.
     */
    private function upgradeDrupalToVersion(string $new_version): void
    {
        $this->say("Upgrading Drupal core to version $new_version...");
        $command = "ddev exec -- composer drupal:core:version-change --version=\"$new_version\" --yes";
        $task = $this->taskExec($command);
        $result = $task->run();
        $success = $result->wasSuccessful();
        if (!$success) {
            throw new AbortTasksException("Error upgrading Drupal core to version $new_version.");
        }
    }

    /**
     * Upgrades Drupal core to the latest stable minor version.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the upgrade to the latest minor version fails.
     */
    private function upgradeDrupalToLatestMinor(): void
    {
        $this->say("Upgrading Drupal core to the latest stable minor version...");
        $command = "ddev exec -- composer drupal:core:version-change --latest-minor --yes";
        $task = $this->taskExec($command);
        $result = $task->run();
        $success = $result->wasSuccessful();
        if (!$success) {
            throw new AbortTasksException("Error upgrading Drupal core to the latest stable minor version.");
        }
    }

    /**
     * Upgrades Drupal core to the latest stable major version.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the upgrade to the latest major version fails.
     */
    private function upgradeDrupalToLatestMajor(): void
    {
        $this->say("Upgrading Drupal core to the latest stable major version...");
        $command = "ddev exec -- composer drupal:core:version-change --latest-major --yes";
        $task = $this->taskExec($command);
        $result = $task->run();
        $success = $result->wasSuccessful();
        if (!$success) {
            throw new AbortTasksException("Error upgrading Drupal core to the latest stable major version.");
        }
    }

    /**
     * Upgrades Drupal core to the next major version.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the upgrade to the next major version fails.
     */
    private function upgradeDrupalToNextMajor(): void
    {
        $this->say("Upgrading Drupal core to the latest stable of the next major version...");
        $command = "ddev exec -- composer drupal:core:version-change --next-major --yes";
        $task = $this->taskExec($command);
        $result = $task->run();
        $success = $result->wasSuccessful();
        if (!$success) {
            throw new AbortTasksException("Error upgrading Drupal core to the latest stable of the next major version.");
        }
    }
}
