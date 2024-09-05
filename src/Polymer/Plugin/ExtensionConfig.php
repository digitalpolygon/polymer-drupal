<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin;

use DigitalPolygon\Polymer\Robo\Config\ExtensionConfigInterface;
use DigitalPolygon\PolymerDrupal\PolymerDrupalServiceProvider;

class ExtensionConfig implements ExtensionConfigInterface
{
    public const NAME = 'digitalpolygon_polymer_drupal';

    /**
     * {@inheritdoc}
     */
    public function getExtensionName(): string
    {
        return ExtensionConfig::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): ?string
    {
        return dirname(__DIR__, 3) . '/config/default.yml';
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceProvider(): ?string
    {
        return PolymerDrupalServiceProvider::class;
    }
}
