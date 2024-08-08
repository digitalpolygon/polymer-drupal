<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use DigitalPolygon\PolymerDrupal\Polymer\Plugin\Tasks\LoadDrushTaskTrait;
use Robo\Common\IO;
use Symfony\Component\Yaml\Yaml;
use Robo\Exception\TaskException;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use DigitalPolygon\Polymer\Robo\Tasks\DrushTask;
use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Exceptions\PolymerException;
use DigitalPolygon\Polymer\Robo\Tasks\Command as PolymerCommand;

class ConfigCommands extends TaskBase
{
    use LoadDrushTaskTrait;
    use IO;

    /**
     * Deploy updates for all multisites.
     *
     * @throws \Robo\Exception\AbortTasksException|TaskException
     */
    #[Command(name: 'drupal:multisite:update-all', aliases: ['dmua'])]
    public function multisiteUpdateAllCommand(): void
    {
        /** @var array<string> $multisites */
        $multisites = $this->getConfigValue('polymer.multisites');

        /** @var PolymerCommand $command */
        $command = new PolymerCommand('drupal:update');
        foreach ($multisites as $multisite) {
            $this->switchSiteContext($multisite);
            $this->invokeCommand($command);
        }
    }

    /**
     * Update the current Drupal site configs.
     *
     * @throws \Robo\Exception\AbortTasksException|TaskException
     */
    #[Command(name: 'drupal:update', aliases: ['du'])]
    public function updateSite(): void
    {
        $task = $this->taskDrush()
        ->stopOnFail()
        ->drush('cr')
        ->drush("updb");

        $result = $task->run();
        if (!$result->wasSuccessful()) {
            throw new PolymerException("Failed to execute database updates!");
        }

        /** @var PolymerCommand[] $commands */
        $commands = [];
        $commands[] = new PolymerCommand('drupal:config:import');
        $commands[] = new PolymerCommand('drupal:deploy:hook');
        $this->invokeCommands($commands);
    }

    /**
     * Imports configuration from the config directory according to cm.strategy.
     *
     * @throws \Robo\Exception\AbortTasksException|TaskException
     */
    #[Command(name: 'drupal:config:import', aliases: ['dcim'])]
    public function import(): mixed
    {
        /** @var \DigitalPolygon\PolymerDrupal\Polymer\Plugin\Tasks\DrushTask $task */
        $task = $this->taskDrush();

        $strategy = $this->getConfigValue('cm.strategy');
        if ($strategy === 'none') {
            $this->logger->warning("CM strategy set to none in polymer.yml. Polymer will NOT import configuration.");
            // Still clear caches to regenerate frontend assets and such.
            return $task->drush("cache-rebuild")->run();
        }

        $this->invokeHook('pre-config-import');

        // If using core-only or config-split strategies, first check to see if
        // required config is exported.
        if (in_array($strategy, ['core-only', 'config-split'])) {
            $core_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.sync.path") . '/core.extension.yml';

            if (!file_exists($core_config_file)) {
                $this->logger->warning("Polymer will NOT import configuration, $core_config_file was not found.");
                // This is not considered a failure.
                return 0;
            }
        }

        // If exported site UUID does not match site active site UUID, set active
        // to equal exported.
        // @see https://www.drupal.org/project/drupal/issues/1613424
        $exported_site_uuid = $this->getExportedSiteUuid();
        if ($exported_site_uuid) {
            $task->drush("config:set system.site uuid $exported_site_uuid");
        }

        switch ($strategy) {
            case 'core-only':
                $this->importCoreOnly($task);
                break;

            case 'config-split':
                // Drush task explicitly to turn on config_split and check if it was
                // successfully enabled. Otherwise default to core-only.
                $check_task = $this->taskDrush();
                $check_task->drush("pm-enable")->arg('config_split');
                $result = $check_task->run();
                if (!$result->wasSuccessful()) {
                    $this->logger->warning('Import strategy is config-split, but the config_split module does not exist. Falling back to core-only.');
                    $this->importCoreOnly($task);
                    break;
                }

                $this->importConfigSplit($task);
                break;
        }

        $task->drush("cache-rebuild");
        $result = $task->run();
        if (!$result->wasSuccessful()) {
            throw new PolymerException("Failed to import configuration!");
        }

        $this->checkConfigOverrides();

        $result = $this->invokeHook('post-config-import');

        return $result;
    }

    /**
   * Import configuration using core config management only.
   *
   * @param mixed $task
   *   Drush task.
   */
    protected function importCoreOnly($task): void
    {
        // @phpstan-ignore method.nonObject
        $task->drush('config:import');
    }

  /**
   * Import configuration using config_split module.
   *
   * @param mixed $task
   *   Drush task.
   */
    protected function importConfigSplit($task): void
    {
        // @phpstan-ignore method.nonObject
        $task->drush('config:import');
        // Runs a second import to ensure splits are
        // both defined and imported.
        // @phpstan-ignore method.nonObject
        $task->drush('config:import');
    }

    /**
     * Checks whether core config is overridden.
     *
     * @throws \Robo\Exception\AbortTasksException|TaskException
     */
    protected function checkConfigOverrides(): void
    {
        if (!$this->getConfigValue('cm.allow-overrides') && !$this->isActiveConfigIdentical()) {
            $task = $this->taskDrush()
            ->stopOnFail()
            ->drush("config-status");
            $result = $task->run();
            if (!$result->wasSuccessful()) {
                throw new PolymerException("Unable to determine configuration status.");
            }
            throw new PolymerException("Configuration in the database does not match configuration on disk. This indicates that your configuration on disk needs attention. Please read https://digitalpolygon.github.io/polymer");
        }
    }

    /**
     * Determines if the active config is identical to sync directory.
     *
     * @return bool
     *   TRUE if config is identical.
     */
    public function isActiveConfigIdentical(): bool
    {
        $task = $this->taskDrush()
            ->stopOnFail()
            ->drush("config:status");
        $result = $task->run();
        $message = trim($result->getMessage());
        $this->logger->debug("Config status check results:");
        $this->logger->debug($message);

        // A successful test here results in "no message" so check for null.
        if ($message == null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the site UUID stored in exported configuration.
     *
     * @return ?string
     */
    protected function getExportedSiteUuid(): ?string
    {
        $site_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.sync.path") . '/system.site.yml';
        if (file_exists($site_config_file)) {
            $site_config = Yaml::parseFile($site_config_file);
            if (is_array($site_config) && isset($site_config['uuid'])) {
                return $site_config['uuid'];
            }
        }

        return null;
    }

    /**
     * Runs drush's deploy hook.
     *
     * @see https://www.drush.org/latest/commands/deploy_hook/
     *
     * @throws \Robo\Exception\AbortTasksException|TaskException
     */
    #[Command(name: 'drupal:deploy:hook', aliases: ['ddh'])]
    public function deployHook(): void
    {
        $task = $this->taskDrush()
        ->stopOnFail()
        // Execute drush's deploy:hook. This runs "deploy" functions.
        // These are one-time functions that run AFTER config is imported.
        ->drush("deploy:hook");

        $result = $task->run();
        if (!$result->wasSuccessful()) {
            throw new PolymerException("Failed to run 'drush deploy:hook'!");
        }
    }

    /**
     * This command adds the polymer-drupal configs in polymer.yml.
     *
     * @throws \Robo\Exception\AbortTasksException|TaskException
     */
    #[Command(name: 'polymer-drupal:init', aliases: ['pdc'])]
    #[Usage(name: 'polymer polymer-drupal:init', description: 'Adds the polymer-drupal configs in polymer.yml.')]
    public function polymerDrupalConfig(): void
    {
        // Get the path to the polymer config file.
        /** @var string $polymer_config_file */
        $polymer_config_file = $this->getConfigValue('polymer.root') . '/config/default.yml';

        // Get the path to the polymer-drupal config file.
        /** @var string $polymer_drupal_config_file */
        $polymer_drupal_config_file = $this->getPolymerDrupalRoot() . '/config/default.yml';

        // Parse the polymer configs file.
        /** @var array $polymer_configs */
        $polymer_configs = Yaml::parseFile($polymer_config_file);
        /** @var array $polymer_drupal_configs */
        $polymer_drupal_configs = Yaml::parseFile($polymer_drupal_config_file);

        // Merge the configs.
        if (is_array($polymer_configs) && is_array($polymer_drupal_configs)) {
            $combined_configs = array_merge($polymer_configs, $polymer_drupal_configs);

            // Dump the combined configs to a YAML string.
            $alteredContents = Yaml::dump($combined_configs, PHP_INT_MAX, 2);

            // Write the altered contents to the polymer config file.
            file_put_contents($polymer_config_file, $alteredContents);
        }
    }

    /**
     * Gets the Polymer Drupal root directory, e.g., /vendor/digitalpolygon/polymer-drupal.
     *
     * @return string
     *   THe filepath for the polymer-drupal root.
     *
     * @throws \Exception
     */
    private function getPolymerDrupalRoot(): string
    {
        $possible_polymer_drupal_roots = [
            dirname(dirname(dirname(dirname(__FILE__)))),
            dirname(dirname(dirname(__FILE__))),
        ];
        foreach ($possible_polymer_drupal_roots as $polymer_drupal_root) {
            if (basename($polymer_drupal_root) !== 'polymer-drupal') {
                continue;
            }
            if (!file_exists("$polymer_drupal_root/src/Polymer/Plugin/Tasks/DrushTask.php")) {
                continue;
            }
            return $polymer_drupal_root;
        }
        throw new \Exception('Could not find the polymer-drupal root directory');
    }
}
