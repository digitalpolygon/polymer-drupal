<?php

namespace DigitalPolygon\PolymerDrupalTest\phpunit\unit;

use DigitalPolygon\Polymer\polymer_drupal\Services\FileSystem;
use DigitalPolygon\Polymer\polymer_drupal\VendorAssets;
use DrupalFinder\DrupalFinderComposerRuntime;
use PHPUnit\Framework\TestCase;

class FileSystemTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/polymer-fs-test-' . bin2hex(random_bytes(4));
        mkdir($this->root, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->root . '/sites')) {
            foreach (array_diff((array) scandir($this->root . '/sites'), ['.', '..']) as $dir) {
                rmdir($this->root . '/sites/' . $dir);
            }
            rmdir($this->root . '/sites');
        }
        rmdir($this->root);
    }

    private function fileSystem(): FileSystem
    {
        $finder = $this->createMock(DrupalFinderComposerRuntime::class);
        $finder->method('getDrupalRoot')->willReturn($this->root);
        return new FileSystem($finder);
    }

    public function testMultisiteDirsExcludePseudoSitesAndSortByName(): void
    {
        foreach (['default', 'zeta', 'alpha', 'g', 'settings'] as $dir) {
            mkdir($this->root . '/sites/' . $dir, 0777, true);
        }

        $this->assertSame(['alpha', 'default', 'zeta'], $this->fileSystem()->getMultisiteDirs());
    }

    public function testMissingSitesDirectoryYieldsNoSites(): void
    {
        $this->assertSame([], $this->fileSystem()->getMultisiteDirs());
    }

    public function testVendorAssetsDirPointsAtPackageSettings(): void
    {
        $this->assertStringEndsWith('/settings', VendorAssets::dir());
        $this->assertDirectoryExists(VendorAssets::dir());
    }
}
