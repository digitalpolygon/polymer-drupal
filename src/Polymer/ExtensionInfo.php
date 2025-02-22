<?php

namespace DigitalPolygon\PolymerDrupal\Polymer;

use Consolidation\Config\ConfigInterface;
use DigitalPolygon\Polymer\Robo\Extension\PolymerExtensionBase;
use DigitalPolygon\PolymerDrupal\Polymer\Services\FileSystem;
use League\Container\DefinitionContainerInterface;

class ExtensionInfo extends PolymerExtensionBase
{
    /**
     * {@inheritdoc}
     */
    public static function getExtensionName(): string
    {
        return 'polymer_drupal';
    }

    /**
     * {@inheritdoc}
     */
    public function setDynamicConfiguration(DefinitionContainerInterface $container, ConfigInterface $config): void
    {
        /** @var FileSystem $drupalFileSystem */
        $drupalFileSystem = $container->get('drupalFileSystem');
        try {
            $config->set('drupal.multisite.sites', $drupalFileSystem->getMultisiteDirs());
        } catch (\OutOfBoundsException $e) {
            // No multisite directories found, use default from config file.
        }

        try {
            $config->set('docroot', $drupalFileSystem->getDrupalRoot());
        } catch (\OutOfBoundsException $e) {
            // No Drupal root found, use default from config file.
        }
    }
}
