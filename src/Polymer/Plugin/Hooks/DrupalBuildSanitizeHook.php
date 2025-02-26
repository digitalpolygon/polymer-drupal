<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Hooks;

use Consolidation\AnnotatedCommand\Attributes\Hook;
use DigitalPolygon\Polymer\Robo\Commands\Artifact\BuildSanitizeCommand;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;

class DrupalBuildSanitizeHook extends BuildSanitizeCommand
{
    #[Hook(type: 'replace-command', target: 'artifact:build:sanitize')]
    public function sanitize(): void
    {
        parent::sanitize();
        $deployDocroot = $this->getConfigValue('deploy.docroot');
        $this->logger->info("Find Drupal core text files...");
        $sanitizeFinder = Finder::create()
            ->files()
            ->name('*.txt')
            ->notName('LICENSE.txt')
            ->in("{$deployDocroot}/core");
        $this->logger->info('Find VCS directories...');
        $vcsFinder = Finder::create()
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->directories()
            ->in([
                $deployDocroot,
                "{$this->deployDir}/vendor",
            ])
            ->name('.git');
        $drush_dir = "{$this->deployDir}/drush";
        if (file_exists($drush_dir)) {
            $vcsFinder->in($drush_dir);
        }
        if ($vcsFinder->hasResults()) {
            $sanitizeFinder->append($vcsFinder);
        }

        $this->logger->info("Find .gitignore files...");
        $gitignoreFinder = Finder::create()
            ->ignoreDotFiles(false)
            ->files()
            ->name('.gitignore')
            ->notPath([
                "sites/g/.gitignore",
                "sites/default/.gitignore",
            ])
            ->in("{$deployDocroot}");
        if ($gitignoreFinder->hasResults()) {
            $sanitizeFinder->append($gitignoreFinder);
        }

        $this->logger->info("Find Github directories...");
        $githubFinder = Finder::create()
            ->ignoreDotFiles(false)
            ->directories()
            ->in([$deployDocroot, "{$this->deployDir}/vendor"])
            ->name('.github');
        if ($githubFinder->hasResults()) {
            $sanitizeFinder->append($githubFinder);
        }

        $this->logger->info('Find INSTALL database text files...');
        $dbInstallFinder = Finder::create()
            ->files()
            ->in([$deployDocroot])
            ->name('/INSTALL\.[a-z]+\.(md|txt)$/');
        if ($dbInstallFinder->hasResults()) {
            $sanitizeFinder->append($dbInstallFinder);
        }

        $this->logger->info('Find other common text files...');
        $filenames = [
            'AUTHORS',
            'CHANGELOG',
            'CONDUCT',
            'CONTRIBUTING',
            'INSTALL',
            'MAINTAINERS',
            'PATCHES',
            'TESTING',
            'UPDATE',
        ];
        $textFileFinder = Finder::create()
            ->files()
            ->in([$deployDocroot])
            ->name('/(' . implode('|', $filenames) . ')\.(md|txt)$/');
        if ($textFileFinder->hasResults()) {
            $sanitizeFinder->append($textFileFinder);
        }

        $this->logger->info("Remove sanitized files from build...");
        $taskFilesystemStack = $this->taskFilesystemStack()
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
        foreach ($sanitizeFinder->getIterator() as $fileInfo) {
            $taskFilesystemStack->remove($fileInfo->getRealPath());
        }
        $taskFilesystemStack->run();
    }
}
