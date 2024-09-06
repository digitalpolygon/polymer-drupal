<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Services;

use DrupalFinder\DrupalFinderComposerRuntime;
use Symfony\Component\Finder\Finder;

class FileSystem
{
    public function __construct(
        protected DrupalFinderComposerRuntime $drupalFinder
    ) {
    }

    public function getDrupalRoot(): string
    {
        return $this->drupalFinder->getDrupalRoot();
    }

    /**
     * Gets an array of Drupal multisite sites.
     *
     * Include sites under docroot/sites, excluding 'all' and acsf 'g'
     * pseudo-sites and 'settings' directory globbed in blt.settings.php.
     *
     * @return array<string>
     *   An array of sites.
     */
    public function getMultisiteDirs(): array
    {
        $sites_dir = $this->drupalFinder->getDrupalRoot() . '/sites';
        $sites = [];

        if (!file_exists($sites_dir)) {
            return $sites;
        }

        $finder = new Finder();
        $dirs = $finder
        ->in($sites_dir)
        ->directories()
        ->depth('< 1')
        ->exclude(['g', 'settings'])
        ->sortByName();
        foreach ($dirs->getIterator() as $dir) {
            $sites[] = $dir->getRelativePathname();
        }

        return $sites;
    }
}
