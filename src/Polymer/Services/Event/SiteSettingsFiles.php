<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Services\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class SiteSettingsFiles extends Event
{
    protected array $settingsFiles = [];
    protected readonly string $site;

    /**
     * Add a settings file that can be globbed.
     *
     * The file should be a complete path.
     *
     * @param $file
     * @return void
     */
    public function addSettingsFile(string $id, string $file): void
    {
        $this->settingsFiles[$id] = $file;
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
