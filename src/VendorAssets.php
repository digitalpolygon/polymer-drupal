<?php

namespace DigitalPolygon\PolymerDrupal;

class VendorAssets
{
    public static function dir(): string
    {
        return dirname(__DIR__) . '/settings';
    }
}
