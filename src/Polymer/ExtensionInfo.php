<?php

namespace DigitalPolygon\PolymerDrupal\Polymer;

use DigitalPolygon\Polymer\Robo\Extension\PolymerExtensionBase;

class ExtensionInfo extends PolymerExtensionBase
{
    /**
     * {@inheritdoc}
     */
    public static function getExtensionName(): string
    {
        return 'polymer_drupal';
    }
}
