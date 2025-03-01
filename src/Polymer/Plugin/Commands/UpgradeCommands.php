<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use DigitalPolygon\PolymerDrupal\Polymer\Plugin\Tasks\LoadDrushTaskTrait;
use Robo\Symfony\ConsoleIO;

class UpgradeCommands extends TaskBase {

    use LoadDrushTaskTrait;

    /**
     * Upgrade Drupal.
     */
    #[Command(name: 'drupal:upgrade', aliases: ['du'])]
    public function upgrade($options = ['yes' => false, 'update' => false, 'update-status' => false, 'update-status-verbose' => false]) {
        $commands = [
            'drupal:upgrade:composer',
            'drupal:upgrade:export',
        ];
    }

    #[Command(name: 'drupal:upgrade:composer', aliases: ['duc'])]
    public function upgradeComposer(): void {
        $this->execCommand('composer drupal:core:version-change --latest-minor');
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
}
