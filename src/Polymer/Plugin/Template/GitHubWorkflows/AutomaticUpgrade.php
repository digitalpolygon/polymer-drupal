<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Template\GitHubWorkflows;

use DigitalPolygon\Polymer\Robo\Template\GitHub\GitHubWorkflowTemplateBase;

class AutomaticUpgrade extends GitHubWorkflowTemplateBase
{
    public const BASE_FILENAME = 'automatic-upgrade.yml';

    public static function id(): string
    {
        return 'drupal-github-automatic-upgrade';
    }

    public function description(): string
    {
        return 'A GitHub workflow for running the configured Drupal upgrade strategy and creating a pull request with all changes.';
    }

    public function source(): string
    {
        return $this
                ->getConfig()
                ->get('extension.polymer_drupal.root') . '/workflows/github/' . self::BASE_FILENAME;
    }

    public function destination(): string
    {
        return $this->getGitHubWorkflowDir() . '/' . self::BASE_FILENAME;
    }

    public static function collections(): array
    {
        $collections = parent::collections();
        $collections[] = 'drupal';
        return $collections;
    }
}
