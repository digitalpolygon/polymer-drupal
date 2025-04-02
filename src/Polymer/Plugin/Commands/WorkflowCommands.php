<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Argument;
use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use DigitalPolygon\Polymer\Robo\Workflow\WorkflowHelperTrait;
use Robo\Exception\TaskException;
use Robo\Symfony\ConsoleIO;

class WorkflowCommands extends TaskBase
{
    use WorkflowHelperTrait;

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
        return $this->generateWorkflowFilesFromExtensionAndConfigKey('polymer_drupal', 'drupal.workflow.files', $platform);
    }
}
