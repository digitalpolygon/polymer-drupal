<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin;

use DigitalPolygon\Polymer\Robo\Config\ExtensionConfigInterface;

class ExtensionConfig implements ExtensionConfigInterface
{
    public function getExtensionName(): string
    {
        return 'digitalpolygon/polymer-drupal';
    }

    public function getConfigFiles(): array
    {
        $configDir = dirname(__DIR__, 3);

        $configurationFiles = [
            'default.yml',
        ];

        return array_map(function ($file) use ($configDir) {
            return $configDir . '/config/' . $file;
        }, $configurationFiles);
    }
}
