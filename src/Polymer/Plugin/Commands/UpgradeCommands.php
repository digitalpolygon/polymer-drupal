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

class UpgradeCommands extends TaskBase {

    use LoadDrushTaskTrait;

    /**
     * Upgrade Drupal.
     */
    #[Command(name: 'drupal:upgrade', aliases: ['du'])]
    #[Option(name: 'new-version', description: 'The specific Drupal core version to upgrade to.')]
    #[Usage(name: 'drupal:upgrade --new-version=10.2.3', description: 'Upgrades Drupal core to version 10.2.3.')]
    public function upgrade(ConsoleIO $io, string|null $new_version = InputOption::VALUE_REQUIRED): void {
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

    #[Command(name: 'drupal:upgrade:composer', aliases: ['duc'])]
    #[Option(name: 'new-version', description: 'The specific Drupal core version to upgrade to.')]
    public function upgradeComposer(ConsoleIO $io, string|null $new_version = InputOption::VALUE_REQUIRED): int {
        $composerPath = $this->getNonProjectComposerPath();
        $upgradeStrategy = $this->getConfigValue('drupal.upgrade.strategy');
        $validOptions = ['latest-minor', 'latest-major', 'next-major', 'semantic'];
        $args = [];
        if ($new_version) {
            $args[] = '--new-version=' . $new_version;
        }
        else {
            if (in_array($upgradeStrategy, $validOptions)) {
                if ('semantic' !== $upgradeStrategy) {
                    $args[] = "--$upgradeStrategy";
                }
            }
            else {
                $io->say("Invalid upgrade strategy: $upgradeStrategy. Valid options are: " . implode(', ', $validOptions));
                $this->logger->error("Invalid upgrade strategy: $upgradeStrategy. Valid options are: " . implode(', ', $validOptions));
                return 1;
            }
        }
        $args = implode(' ', $args);
        if ($composerPath) {
            $this->execCommand("$composerPath drupal:core:version-change $args");
        }

    }

    #[Command(name: 'drupal:upgrade:export', aliases: ['due'])]
    public function exportChanges(ConsoleIO $io): void {
        $configManagementStrategy = $this->getConfigValue('drupal.cm.strategy');
        if (in_array($configManagementStrategy, ['config-split', 'core-only'])) {
            $this->taskDrush()
                ->stopOnFail()
                ->interactive($io->input()->isInteractive())
                ->drush('updb')
                ->drush('cex')
                ->run();
        }
    }

    protected function getNonProjectComposerPath(): string|false {
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
