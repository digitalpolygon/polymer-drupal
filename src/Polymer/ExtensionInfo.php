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
        $multiSiteDirs = $drupalFileSystem->getMultisiteDirs();
        $config
            ->set('drupal.multisite.sites', $multiSiteDirs)
            ->set('docroot', $drupalFileSystem->getDrupalRoot());
    }
}
