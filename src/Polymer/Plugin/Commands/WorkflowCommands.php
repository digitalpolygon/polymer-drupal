<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Argument;
use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use Robo\Exception\TaskException;
use Robo\Symfony\ConsoleIO;

class WorkflowCommands extends TaskBase
{
    /**
     * Generate workflows for the specified platform.
     *
     * @param ConsoleIO $io
     *   The console input/output object.
     * @param string $platform
     *   The platform to generate workflows for.
     *
     * @return int
     *   The exit code of the command.
     */
    #[Command(name: 'drupal:workflow:generate', aliases: ['dwg'])]
    #[Argument(name: 'platform', description: 'The platform to generate workflows for.')]
    public function generateWorkflows(ConsoleIO $io, string $platform): int
    {
        $this->say("Generating workflows for platform: <comment>$platform</comment>");
        if (empty($platformDir = $this->getPlatformDirectory($platform))) {
            $this->logger->error("Error: <error>Invalid platform: $platform</error>");
            return 1;
        }
        $polymerRoot = $this->getConfigValue('extension.polymer_drupal.root');
        $workflowsDir = "$polymerRoot/workflows/$platform";
        $configuredWorkflows = $this->getConfigValue('drupal.workflow.files.github');
        $workflowFilePaths = array_map(function ($file) use ($workflowsDir) {
            return "$workflowsDir/$file";
        }, $configuredWorkflows);
        $workflowFilePaths = array_filter($workflowFilePaths, 'is_file');
        if (is_dir($workflowsDir)) {
            $task = $this->taskFilesystemStack()
                ->stopOnFail();
            if (!is_dir($platformDir)) {
                $task->mkdir($platformDir);
            }
            foreach ($workflowFilePaths as $workflowFilePath) {
                $file = basename($workflowFilePath);
                $file = "$platformDir/$file";
                $task->copy($workflowFilePath, $file);
            }
            try {
                $task->run();
            } catch (TaskException $e) {
                $this->logger->error("Error: <error>{$e->getMessage()}</error>");
                return 1;
            }
        }
        return 0;
    }

    protected function getPlatformDirectory(string $platform): string|false
    {
        $polymerRoot = $this->getConfigValue('repo.root');
        if ('github' === $platform) {
            return "$polymerRoot/.github/workflows";
        }
        return false;
    }
}
