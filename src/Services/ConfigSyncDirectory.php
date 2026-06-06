<?php

namespace DigitalPolygon\Polymer\polymer_drupal\Services;

use Symfony\Component\Yaml\Yaml;

/**
 * Inspects an exported Drupal configuration sync directory.
 *
 * Pure filesystem/YAML logic extracted from ConfigCommands so it is unit
 * testable. Stateless on purpose: the sync path depends on the active site
 * context (--site), so callers resolve it from configuration per call.
 */
class ConfigSyncDirectory
{
    /**
     * Whether core configuration has been exported to the sync directory.
     *
     * Used as the "is there anything to import" gate for the core-only and
     * config-split import strategies.
     */
    public function isCoreConfigExported(string $syncDir): bool
    {
        return file_exists($syncDir . '/core.extension.yml');
    }

    /**
     * Returns the site UUID stored in exported configuration, if any.
     *
     * @see https://www.drupal.org/project/drupal/issues/1613424
     */
    public function exportedSiteUuid(string $syncDir): ?string
    {
        $siteConfigFile = $syncDir . '/system.site.yml';
        if (!file_exists($siteConfigFile)) {
            return null;
        }
        $siteConfig = Yaml::parseFile($siteConfigFile);
        if (is_array($siteConfig) && isset($siteConfig['uuid']) && is_string($siteConfig['uuid'])) {
            return $siteConfig['uuid'];
        }

        return null;
    }
}
