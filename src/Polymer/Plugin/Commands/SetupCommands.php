<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Commands;

use Consolidation\AnnotatedCommand\Attributes\Command;
use DigitalPolygon\Polymer\Robo\Tasks\TaskBase;
use Grasmash\Expander\Expander;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class SetupCommands extends TaskBase
{
    #[Command(name: 'drupal:setup:site:all', aliases: ['dssa'])]
    public function setupSiteAll(ConsoleIO $io): void
    {
        $sites = $this->getConfigValue('drupal.multisite.sites');
        foreach ($sites as $site) {
            $this->commandInvoker->pinGlobal('--site', $site);
            $this->commandInvoker->invokeCommand($io->input(), 'drupal:setup:site');
            $this->commandInvoker->unpinGlobal('--site');
        }
    }

    #[Command(name: 'drupal:setup:site', aliases: ['dss'])]
    public function setupSite(ConsoleIO $io): void
    {
        $site = $io->input()->getOption('site');
        $io->say("Setting up Drupal $site site...");
        $defaultArtifact = $this->getConfigValue('project.default-artifact');
        $commands = [
            'drupal:setup:site:files',
        ];
        if (!empty($defaultArtifact)) {
            $build_dependencies = $this->getConfigValue('artifacts.' . $defaultArtifact . '.dependent-builds');
            if (is_array($build_dependencies)) {
                foreach ($build_dependencies as $build_dependency) {
                    $commands[] = [
                        'command' => 'build',
                        'args' => ['target' => $build_dependency],
                    ];
                }
            }
        }

        if (getenv('IS_DDEV_PROJECT') == 'true') {
            $commands[] = 'drupal:setup:ddev';
        }

        switch ($this->getConfigValue('drupal.setup.strategy')) {
            case 'install':
                $commands[] = 'drupal:site:install';
                break;
            case 'sync':
                $commands[] = 'drupal:site:sync';
                break;
        }

        $this->commandInvoker->pinGlobal('--site', $site);
        foreach ($commands as $command) {
            if (is_array($command)) {
                $this->commandInvoker->invokeCommand($io->input(), $command['command'], $command['args']);
            } else {
                $this->commandInvoker->invokeCommand($io->input(), $command);
            }
        }
        $this->commandInvoker->unpinGlobal('--site');
    }

    #[Command(name: 'drupal:setup:site:files', aliases: ['dssf'])]
    public function setupSiteFiles(ConsoleIO $io): void
    {
        $io->say('Setting up Drupal site files...');
        $site = $io->input()->getOption('site');
        $this->addGlobalSiteSettings();
        $this->addFilesToSite($site);
        $this->appendPolymerSettingsInclusion($site);
    }

    /**
     * Append polymer.settings.php to end of settings.php file for site.
     *
     * @param string $site
     *   The site whose settings.php file should be appended.
     *
     * @return void
     */
    protected function appendPolymerSettingsInclusion(string $site): void
    {
        // Include polymer.settings.php in settings.php at the end of the file.
        $docroot = $this->getConfigValue('docroot');
        $sitePath = "$docroot/sites/$site";
        $settingsFile = "$sitePath/settings.php";
        $includeStartDelim = '@polymer-settings-include-start';
        $includeEndDelim = '@polymer-settings-include-start';
        $lines = file($settingsFile);
        $startLine = null;
        $endLine = null;
        $includeString = <<<INCLUDE

// @polymer-settings-include-start - do not remove this line
/**
 * IMPORTANT.
 *
 * Do not include additional settings here. Instead, add them to settings
 * included by `polymer.settings.php`. See Polymer's documentation for more detail.
 *
 * @link https://digitalpolygon.github.io/polymer-drupal/
 */
// Polymer assumes that this inclusion always comes at the end of the file.
\$polymer_settings_file = DRUPAL_ROOT . '/../vendor/digitalpolygon/polymer-drupal/settings/polymer.settings.php';
if (file_exists(\$polymer_settings_file)) {
  require \$polymer_settings_file;
}
// @polymer-settings-include-end - do not remove this line

INCLUDE;

        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, $includeStartDelim) !== false) {
                $startLine = $lineNumber;
            }
            if (strpos($line, $includeEndDelim) !== false) {
                $endLine = $lineNumber;
            }
        }
        $this->say('Adding polymer.settings.php inclusion to settings.php...');
        if ($startLine === null && $endLine === null) {
            $settingsText = implode($lines) . $includeString;
            file_put_contents($settingsFile, $settingsText);
            $this->say('polymer.settings.php inclusion added to settings.php.');
        } else {
            $this->say('polymer.settings.php inclusion already exists in settings.php, skipping.');
        }
    }

    /**
     * Add global site settings directory and template global settings files.
     *
     * @return void
     */
    protected function addGlobalSiteSettings(): void
    {
        // Create sites/settings directory if it doesn't exist.
        // Create sites/settings/default.global.settings.php if it doesn't exist.
        $docroot = $this->getConfigValue('docroot');
        $polymerDrupalRoot = $this->getConfigValue('extension.polymer_drupal.root');
        $globalSettingsTemplateFile = "$polymerDrupalRoot/settings/default.global.settings.php";
        $globalSettingsDirectory = "$docroot/sites/settings";

        $filesToCopy = [
            $globalSettingsTemplateFile => "$globalSettingsDirectory/default.global.settings.php",
        ];

        $task = $this->taskFilesystemStack()
            ->stopOnFail()
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);

        if (!is_dir($globalSettingsDirectory)) {
            $task->mkdir($globalSettingsDirectory);
        }
        foreach ($filesToCopy as $source => $destination) {
            if (!file_exists($destination)) {
                $task->copy($source, $destination);
            }
        }

        $task->run();
    }

    /**
     * Add scaffolding files to site directory.
     *
     * @param string $site
     *   The site to add scaffold files to.
     *
     * @return void
     */
    protected function addFilesToSite(string $site): void
    {
        // All sites need to have:
        // - settings.php
        // - settings.local.php
        // - default.local.drush.yml
        // - local.drush.yml
        // - settings/
        //   - default.includes.settings.php
        $docroot = $this->getConfigValue('docroot');
        $polymerDrupalRoot = $this->getConfigValue('extension.polymer_drupal.root');
        $sitesDir = "$docroot/sites";
        $siteDir = "$sitesDir/$site";
        $siteSettingsDir = "$siteDir/settings";
        $polymerDefaultIncludesFile = "$polymerDrupalRoot/settings/default.includes.settings.php";
        $siteDefaultIncludesFile = "$siteSettingsDir/default.includes.settings.php";
        // Use the local settings file provided by Drupal scaffolding.
        $scaffoldExampleSettingsFile = "$sitesDir/example.settings.local.php";
        $siteLocalSettingsFile = "$siteDir/settings.local.php";
        $scaffoldDefaultSettingsFile = "$sitesDir/default/default.settings.php";
        $siteSpecificDefaultSettingsFile = "$siteDir/default.settings.php";
        $siteSettingsFile = "$siteDir/settings.php";
        $polymerLocalDrushFile = "$polymerDrupalRoot/settings/default.local.drush.yml";
        $siteLocalDrushFileDefault = "$siteDir/default.local.drush.yml";
        $siteLocalDrushFile = "$siteDir/local.drush.yml";
        $defaultSettingsFileToUse = file_exists($siteSpecificDefaultSettingsFile) ? $siteSpecificDefaultSettingsFile : $scaffoldDefaultSettingsFile;

        if (!file_exists($defaultSettingsFileToUse)) {
            $this->logger->warning("No default settings file could be found.");
        }

        $filesToCopy = [
            $polymerDefaultIncludesFile => $siteDefaultIncludesFile,
            $scaffoldExampleSettingsFile => $siteLocalSettingsFile,
            $polymerLocalDrushFile => $siteLocalDrushFileDefault,
            $siteLocalDrushFileDefault => $siteLocalDrushFile,
            $defaultSettingsFileToUse => $siteSettingsFile,
        ];

        $expandFiles = [
            $siteLocalDrushFile,
        ];

        $task = $this->taskFilesystemStack()
            ->stopOnFail()
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);

        if (!is_dir($siteSettingsDir)) {
            $task->mkdir($siteSettingsDir);
        }
        foreach ($filesToCopy as $source => $destination) {
            if (file_exists($source)) {
                $task->copy($source, $destination);
            }
        }
        $task->run();

        // Expand files.
        $expander = new Expander();
        foreach ($expandFiles as $file) {
            if (file_exists($file)) {
                $expandedContent = $expander->expandArrayProperties(file($file), $this->getConfig()->export());
                file_put_contents($file, $expandedContent);
            }
        }
    }

    #[Command(name: 'drupal:setup:ddev', aliases: ['dssdd'])]
    public function setupDdev(ConsoleIO $io): void
    {
        $io->say("Generating DDEV config and creating databases...");
        $repoRoot = $this->getConfigValue('repo.root');
        $taskCreateDatabases = $this->taskExecStack()
            ->interactive($io->input()->isInteractive())
            ->stopOnFail()
            ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE);
        $ddevDir = $repoRoot . '/.ddev';
        $polymerDdevConfigFile = "$ddevDir/config.polymer.yml";
        if (!is_dir($ddevDir)) {
            $this->say('Could not detect DDEV directory. Skipping DDEV setup.');
            return;
        }
        $multisites = $this->getConfigValue('drupal.multisite.sites', []);
        $databaseString = 'CREATE DATABASE IF NOT EXISTS #site#; GRANT ALL ON #site#.* TO "db"@"%";';
        $config = [
            'hooks' => [],
        ];
        $postStartHooks = [];
        foreach ($multisites as $multisite) {
            if ('default' === $multisite) {
                // DDEV already writes DDEV settings file to default site.
                continue;
            }
            $dbCreateString = "mysql -e '" . str_replace('#site#', $multisite, $databaseString) . "'";
            $postStartHooks[] = [
                'exec' => $dbCreateString,
            ];
            $taskCreateDatabases->exec($dbCreateString);
        }
        $config['hooks']['post-start'] = $postStartHooks;
        file_put_contents($polymerDdevConfigFile, Yaml::dump($config, 4));
        $taskCreateDatabases->run();
    }
}
