<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Argument;
use Consolidation\AnnotatedCommand\Attributes\Command;
use Consolidation\AnnotatedCommand\Attributes\DefaultFields;
use Consolidation\AnnotatedCommand\Attributes\Hook;
use Consolidation\AnnotatedCommand\Attributes\Option;
use Consolidation\AnnotatedCommand\Attributes\Usage;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use DigitalPolygon\PolymerDrupal\Polymer\Plugin\Tasks\LoadDrushTaskTrait;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Input\InputOption;

class UpgradeCommands extends TaskBase
{
    use LoadDrushTaskTrait;

    /**
     * Upgrade Drupal codebase and export changes.
     *
     * Part of this operation ensures that all multisites are updated
     * and their configuration is exported, if the site uses a
     * configuration management strategy that exports config.
     *
     * @param ConsoleIO $io
     *   The console input/output object.
     * @param string|null|int $new_version
     *   If provided, this will target a specific Drupal version to upgrade to.
     *
     * @return void
     */
    #[Command(name: 'drupal:upgrade', aliases: ['du'])]
    #[Option(name: 'new-version', description: 'The specific Drupal core version to upgrade to.')]
    #[Usage(name: 'drupal:upgrade --new-version=10.2.3', description: 'Upgrades Drupal core to version 10.2.3.')]
    public function upgrade(ConsoleIO $io, string|null|int $new_version = InputOption::VALUE_REQUIRED): void
    {
        $multisites = $this->getConfigValue('drupal.multisite.sites');
        $args = [];
        if ($new_version) {
            $args['--new-version'] = $new_version;
        }
        $this->commandInvoker->invokeCommand($io->input(), 'drupal:upgrade:composer', $args);
        foreach ($multisites as $multisite) {
            $this->commandInvoker->pinGlobal('--site', $multisite);
            $this->commandInvoker->invokeCommand($io->input(), 'drupal:upgrade:export');
            $this->commandInvoker->unpinGlobal('--site');
        }
    }

    /**
     * Upgrade Drupal codebase based on upgrade strategy.
     *
     * @param ConsoleIO $io
     *   The console input/output object.
     * @param string|int|null $new_version
     *   If provided, this will target a specific Drupal version to upgrade to.
     * @return int
     *  The exit code of the command.
     *
     * @throws \Robo\Exception\AbortTasksException
     * @throws \Robo\Exception\TaskException
     */
    #[Command(name: 'drupal:upgrade:composer', aliases: ['duc'])]
    #[Option(name: 'new-version', description: 'The specific Drupal core version to upgrade to.')]
    public function upgradeComposer(ConsoleIO $io, string|null|int $new_version = InputOption::VALUE_REQUIRED): int
    {
        $composerPath = $this->getNonProjectComposerPath();
        $command = 'drupal:core:version-change';
        if (!$composerPath) {
            $this->logger->error("No composer binary found outside the repository directory. Polymer can't run the upgrade process with project-level composer binary.");
            return 1;
        }
        $upgradeStrategy = $this->getConfigValue('drupal.upgrade.strategy');
        $validOptions = ['latest-minor', 'latest-major', 'next-major', 'semantic'];
        $args = [];
        if ($new_version) {
            $args[] = '--new-version=' . $new_version;
        } else {
            if (in_array($upgradeStrategy, $validOptions)) {
                if ($upgradeStrategy === 'semantic') {
                    $command = 'update';
                } else {
                    $args[] = "--$upgradeStrategy";
                }
            } else {
                $this->logger->error("Invalid upgrade strategy: $upgradeStrategy. Valid options are: " . implode(', ', $validOptions));
                return 1;
            }
        }
        $args = implode(' ', $args);

        return $this->execCommand("$composerPath $command $args --yes --no-interaction");
    }

    /**
     * Run database updates and export configuration for a site.
     *
     * Note that configuration will only be exported if the site's
     * configuration management strategy is one of the following:
     *
     *   - config-split
     *   - core-only
     *
     * @param ConsoleIO $io
     * @return void
     *
     * @throws \Robo\Exception\TaskException
     */
    #[Command(name: 'drupal:upgrade:export', aliases: ['due'])]
    public function exportChanges(ConsoleIO $io): void
    {
        $task = $this->taskDrush()
            ->stopOnFail()
            ->interactive($io->input()->isInteractive())
            ->drush('updb');
        $configManagementStrategy = $this->getConfigValue('drupal.cm.strategy');
        if (in_array($configManagementStrategy, ['config-split', 'core-only'])) {
            $task->drush('cex');
        }
        $task->run();
    }

    protected function getNonProjectComposerPath(): string|false
    {
        // Find a composer binary not located within the repository directory.
        $repoRoot = $this->getConfigValue('repo.root');
        if (!empty($path = getenv('PATH'))) {
            $paths = explode(PATH_SEPARATOR, $path);
            foreach ($paths as $path) {
                if (strpos($path, $repoRoot) === 0) {
                    continue;
                }
                $composerPath = $path . '/composer';
                if (file_exists($composerPath)) {
                    return $composerPath;
                }
            }
        }
        return false;
    }
}
