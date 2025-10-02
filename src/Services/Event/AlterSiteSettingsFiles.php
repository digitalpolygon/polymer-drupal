<?php

namespace DigitalPolygon\Polymer\polymer_drupal\Services\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class AlterSiteSettingsFiles extends Event
{
    protected array $settingsFiles = [];
    protected string $site;

    /**
     * Add a settings file that can be globbed.
     *
     * The file should be a complete path.
     *
     * @param array $settingsFiles
     * @return void
     */
    public function setSettingsFiles(array $settingsFiles): void
    {
        $this->settingsFiles = $settingsFiles;
    }

    /**
     * @return array
     */
    public function getSettingsFiles(): array
    {
        return $this->settingsFiles;
    }

    /**
     * @param string $site
     * @return void
     */
    public function setSite(string $site): void
    {
        $this->site = $site;
    }

    /**
     * @return string
     */
    public function getSite(): string
    {
        return $this->site;
    }
}
