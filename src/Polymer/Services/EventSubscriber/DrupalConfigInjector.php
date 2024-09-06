<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Services\EventSubscriber;

use Consolidation\Config\Loader\YamlConfigLoader;
use DigitalPolygon\Polymer\Robo\Config\PolymerConfig;
use DigitalPolygon\PolymerDrupal\Polymer\Plugin\ExtensionInfo;
use DigitalPolygon\PolymerDrupal\Polymer\Services\FileSystem;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\GlobalOptionsEventListener;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DrupalConfigInjector extends GlobalOptionsEventListener implements EventSubscriberInterface, ConfigAwareInterface, ContainerAwareInterface
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;

    public function __construct(protected FileSystem $drupalFileSystem)
    {
        parent::__construct();
    }

    public function injectEnvironmentConfig(ConsoleCommandEvent $event): void
    {
        $this->addDynamicConfiguration($event);
        $this->addEnvironmentConfiguration($event);
    }

    protected function addDynamicConfiguration(ConsoleCommandEvent $event): void
    {
        /** @var PolymerConfig $config */
        $config = $this->getConfig();
        $extensionConfig = $config->getContext(ExtensionInfo::NAME);

        $multiSiteDirs = $this->drupalFileSystem->getMultisiteDirs();
        $extensionConfig
            ->set('drupal.multisites', $multiSiteDirs)
            ->set('docroot', $this->drupalFileSystem->getDrupalRoot());
    }

    protected function addEnvironmentConfiguration(ConsoleCommandEvent $event): void
    {
        /** @var PolymerConfig $config */
        $config = $this->getConfig();
        $input = $event->getInput();

        $globalOptions = $config->get($this->prefix, []);
        if ($config instanceof \Consolidation\Config\GlobalOptionDefaultValuesInterface) {
            $globalOptions += $config->getGlobalOptionDefaultValues();
        }

        $globalOptions += $this->applicationOptionDefaultValues();
        if (array_key_exists('environment', $globalOptions) && array_key_exists('site', $globalOptions))
        {
            $default = $globalOptions['environment'];
            $environment = $input->hasOption('environment') ? $input->getOption('environment') : null;
            if (!isset($environment)) {
                $environment = $default;
            }
            $default = $globalOptions['site'];
            $site = $input->hasOption('site') ? $input->getOption('site') : null;
            if (!isset($site)) {
                $site = $default;
            }
            $sitePath = $this->drupalFileSystem->getDrupalRoot() . '/sites/' . $site;
            $siteConfigFilePath = $sitePath . '/polymer.yml';
            $siteEnvironmentConfigFilePath = $sitePath . '/' . $environment . '.polymer.yml';
            $siteConfig = $config->getContext('site');
            $siteEnvironmentConfig = $config->getContext('site_environment');
            $loader = new YamlConfigLoader();
            $siteData = $loader
                ->load($siteConfigFilePath)
                ->export();
            $siteConfig->replace($siteData);
            $loader = new YamlConfigLoader();
            $siteEnvironmentData = $loader
                ->load($siteEnvironmentConfigFilePath)
                ->export();
            $siteEnvironmentConfig->replace($siteEnvironmentData);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => [
                ['injectEnvironmentConfig', -1],
            ],
        ];
    }
}
