<?php

namespace DigitalPolygon\Polymer\polymer_drupal;

class VendorAssets
{
    public static function dir(): string
    {
        return dirname(__DIR__) . '/settings';
    }
}
