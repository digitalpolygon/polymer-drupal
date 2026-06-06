<?php

namespace DigitalPolygon\PolymerDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\polymer_drupal\Services\ConfigSyncDirectory;
use PHPUnit\Framework\TestCase;

/**
 * Tier-1 methodology validation (PWT-159): pure sync-directory inspection
 * logic extracted from ConfigCommands::import() into a unit-testable
 * service.
 */
class ConfigSyncDirectoryTest extends TestCase
{
    private string $syncDir;

    private ConfigSyncDirectory $service;

    protected function setUp(): void
    {
        $this->syncDir = sys_get_temp_dir() . '/polymer-sync-test-' . bin2hex(random_bytes(4));
        mkdir($this->syncDir, 0777, true);
        $this->service = new ConfigSyncDirectory();
    }

    protected function tearDown(): void
    {
        foreach ((array) glob($this->syncDir . '/*') as $file) {
            if (is_string($file)) {
                unlink($file);
            }
        }
        rmdir($this->syncDir);
    }

    public function testCoreConfigNotExportedInEmptyDirectory(): void
    {
        $this->assertFalse($this->service->isCoreConfigExported($this->syncDir));
    }

    public function testCoreConfigExportedWhenCoreExtensionFileExists(): void
    {
        file_put_contents($this->syncDir . '/core.extension.yml', "module: {}\n");

        $this->assertTrue($this->service->isCoreConfigExported($this->syncDir));
    }

    public function testExportedSiteUuidIsReadFromSystemSiteYml(): void
    {
        file_put_contents(
            $this->syncDir . '/system.site.yml',
            "uuid: 2cd80f80-2f33-4d2f-a484-d97da7b962f5\nname: Fixture\n"
        );

        $this->assertSame(
            '2cd80f80-2f33-4d2f-a484-d97da7b962f5',
            $this->service->exportedSiteUuid($this->syncDir)
        );
    }

    public function testExportedSiteUuidIsNullWithoutSystemSiteYml(): void
    {
        $this->assertNull($this->service->exportedSiteUuid($this->syncDir));
    }

    public function testExportedSiteUuidIsNullWhenUuidKeyIsMissing(): void
    {
        file_put_contents($this->syncDir . '/system.site.yml', "name: Fixture\n");

        $this->assertNull($this->service->exportedSiteUuid($this->syncDir));
    }

    public function testExportedSiteUuidIsNullForNonStringUuid(): void
    {
        file_put_contents($this->syncDir . '/system.site.yml', "uuid: [not, a, string]\n");

        $this->assertNull($this->service->exportedSiteUuid($this->syncDir));
    }
}
