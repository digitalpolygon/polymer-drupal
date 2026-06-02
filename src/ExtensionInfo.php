<?php

namespace DigitalPolygon\Polymer\polymer_drupal;

use Consolidation\Config\ConfigInterface;
use DigitalPolygon\Polymer\Core\Robo\Extension\PolymerExtensionBase;
use DigitalPolygon\Polymer\polymer_drupal\Services\FileSystem;
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
    public function setDynamicConfiguration(DefinitionContainerInterface $container, array &$config): void
    {
        /** @var FileSystem $drupalFileSystem */
        $drupalFileSystem = $container->get('drupalFileSystem');
        try {
            $config['drupal.multisite.sites'] = $drupalFileSystem->getMultisiteDirs();
        } catch (\OutOfBoundsException $e) {
            // No multisite directories found, use default from config file.
        }

        try {
            $config['docroot'] = $drupalFileSystem->getDrupalRoot();
        } catch (\OutOfBoundsException $e) {
            // No Drupal root found, use default from config file.
        }
    }
}
