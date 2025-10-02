<?php

namespace DigitalPolygon\Polymer\polymer_drupal\Plugin\Tasks;

/**
 * Load the Drush Robo task.
 *
 * This trait is intended to be used in command classes that require a Drush task
 * for interacting with Drupal using Robo. It sets up the Drush task and configures
 * its verbosity based on the current output settings.
 */
trait LoadDrushTaskTrait
{
    /**
     * Initializes and returns a configured Drush task instance.
     *
     * @return \DigitalPolygon\Polymer\polymer_drupal\Plugin\Tasks\DrushTask
     *   Drush task.
     */
    protected function taskDrush()
    {
        /** @var \DigitalPolygon\Polymer\polymer_drupal\Plugin\Tasks\DrushTask $task */
        $task = $this->task(DrushTask::class);
        /** @var \Symfony\Component\Console\Output\OutputInterface $output */
        $output = $this->output();
        $task->setVerbosityThreshold($output->getVerbosity());

        return $task;
    }
}
