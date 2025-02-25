<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\Option;
use Consolidation\AnnotatedCommand\Attributes\Usage;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use DigitalPolygon\PolymerDrupal\Polymer\Plugin\Tasks\LoadDrushTaskTrait;
use Robo\Exception\AbortTasksException;
use Robo\Exception\TaskException;

/**
 * Provides commands for upgrading Drupal core.
 *
 * This class defines commands to automate the upgrade process for Drupal core,
 * including version updates, database updates, cache clearing, and configuration export.
 */
class DrupalUpgradeCommands extends TaskBase
{
    use LoadDrushTaskTrait;

    /**
     * Upgrades Drupal core based on the provided options.
     *
     * This method manages the full Drupal upgrade process:
     * - Executes the selected Drupal upgrade strategy.
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
    #[Option(name: 'new_version', description: 'The specific Drupal core version to upgrade to.')]
    #[Option(name: 'latest_minor', description: 'Upgrade to the latest stable minor version of Drupal core.')]
    #[Option(name: 'latest_major', description: 'Upgrade to the latest stable major version of Drupal core.')]
    #[Option(name: 'next_major', description: 'Upgrade to the next major version of Drupal core.')]
    #[Usage(name: 'drupal:upgrade --new_version=10.2.3', description: 'Upgrades Drupal core to version 10.2.3.')]
    #[Usage(name: 'drupal:upgrade --latest_minor', description: 'Upgrades Drupal core to the latest stable minor version.')]
    #[Usage(name: 'drupal:upgrade --latest_major', description: 'Upgrades Drupal core to the latest stable major version.')]
    #[Usage(name: 'drupal:upgrade --next_major', description: 'Upgrades Drupal core to the next major version.')]
    public function upgradeDrupal(string $new_version = null, bool $latest_minor = false, bool $latest_major = false, bool $next_major = false): void
    {
        $this->doUpgrade($new_version, $latest_minor, $latest_major, $next_major);
        $this->applyDatabaseUpdates();
        $this->clearCache();
        $this->exportConfiguration();
        $this->say("Drupal upgrade process completed successfully.");
    }

    /**
     * Determines and executes the appropriate Drupal upgrade strategy based on provided options.
     *
     * @param string|null $new_version
     *   The specific Drupal version to upgrade to.
     * @param bool $latest_minor
     *   If TRUE, upgrades to the latest stable minor version.
     * @param bool $latest_major
     *   If TRUE, upgrades to the latest stable major version.
     * @param bool $next_major
     *   If TRUE, upgrades to the next major version.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if no valid upgrade option is selected.
     */
    private function doUpgrade(string $new_version = null, bool $latest_minor = false, bool $latest_major = false, bool $next_major = false): void
    {
        if ($new_version) {
            $this->upgradeToVersion($new_version);
            return;
        }
        if ($latest_minor) {
            $this->upgradeDrupalToLatestMinor();
            return;
        }
        if ($latest_major) {
            $this->upgradeToLatestMajor();
            return;
        }
        if ($next_major) {
            $this->upgradeToNextMajor();
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
    private function exportConfiguration(): void
    {
        $this->runDrushCommand('config:export', 'Exporting configuration...');
    }

    /**
     * Clears Drupal cache using Drush.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the cache clearing fails.
     */
    private function clearCache(): void
    {
        $this->runDrushCommand('cache:rebuild', 'Clearing cache...');
    }

    /**
     * Applies database updates using Drush.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if applying database updates fails.
     */
    private function applyDatabaseUpdates(): void
    {
        $this->runDrushCommand('updatedb', 'Applying database updates...');
    }

    /**
     * Upgrades Drupal core to a specific version using Composer.
     *
     * @param string $new_version
     *   The new Drupal version to upgrade to.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the upgrade to the specified version fails.
     */
    private function upgradeToVersion(string $new_version): void
    {
        $this->say("Upgrading Drupal core to version $new_version...");
        $this->runComposerCommand("drupal:core:version-change --version=\"$new_version\" --yes");
    }

    /**
     * Upgrades Drupal core to the latest stable minor version using Composer.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the upgrade to the latest minor version fails.
     */
    private function upgradeDrupalToLatestMinor(): void
    {
        $this->say('Upgrading Drupal core to the latest stable minor version...');
        $this->runComposerCommand('drupal:core:version-change --latest-minor --yes');
    }

    /**
     * Upgrades Drupal core to the latest stable major version using Composer.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the upgrade to the latest major version fails.
     */
    private function upgradeToLatestMajor(): void
    {
        $this->say('Upgrading Drupal core to the latest stable major version...');
        $this->runComposerCommand('drupal:core:version-change --latest-major --yes');
    }

    /**
     * Upgrades Drupal core to the next major version using Composer.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the upgrade to the next major version fails.
     */
    private function upgradeToNextMajor(): void
    {
        $this->say('Upgrading Drupal core to the latest stable of the next major version...');
        $this->runComposerCommand('drupal:core:version-change --next-major --yes');
    }

    /**
     * Runs a Composer command and handles the result.
     *
     * @param string $command
     *   The Composer command to run.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the Composer command fails.
     */
    private function runComposerCommand(string $command): void
    {
        $is_verbose = $this->output()->isVerbose();
        // Define the composer task to run.
        /** @var \Robo\Task\Base\Exec $task */
        $task = $this->taskExecStack()
            ->exec("composer $command")
            ->printMetadata($is_verbose)
            ->printOutput($is_verbose)
            ->interactive($this->input()->isInteractive())
            ->stopOnFail();
        // Define the working dir where the Composer command will run.
        /** @var string $dir */
        $dir = $this->getConfigValue('repo.root');
        if (!empty($dir)) {
            $task->dir($dir);
        }
        // Run the composer command.
        $result = $task->run();
        // Validate the result.
        if (!$result->wasSuccessful()) {
            throw new AbortTasksException("Error executing command: $command");
        }
    }

    /**
     * Runs a Drush command and handles the result.
     *
     * @param string $drushCommand
     *   The Drush command to run.
     * @param string $description
     *   A description of the task being performed.
     *
     * @throws \Robo\Exception\AbortTasksException
     *   Thrown if the Drush command fails.
     */
    private function runDrushCommand(string $drushCommand, string $description): void
    {
        // Show Confirmation message.
        $this->say($description);
        // Define the Drush task to run in no-interaction mode.
        $task = $this->taskDrush();
        $task->drush($drushCommand);
        $task->option('--yes');
        // Define the working dir where the Drush command will run.
        /** @var string $dir */
        $dir = $this->getConfigValue('repo.root');
        if (!empty($dir)) {
            $task->dir($dir);
        }
        // Run the drush command.
        try {
            $result = $task->run();
            if (!$result->wasSuccessful()) {
                throw new AbortTasksException("Error during: $description");
            }
        } catch (TaskException $e) {
            throw new AbortTasksException($e->getMessage());
        }
    }
}
