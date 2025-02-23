<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Services\EventSubscriber;

use Consolidation\Config\Config;
use Consolidation\Config\Loader\YamlConfigLoader;
use DigitalPolygon\Polymer\Robo\Config\PolymerConfig;
use DigitalPolygon\Polymer\Robo\Event\PolymerEvents;
use DigitalPolygon\Polymer\Robo\Event\PostInvokeCommandEvent;
use DigitalPolygon\PolymerDrupal\Polymer\Services\FileSystem;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\Application;
use Robo\Common\ConfigAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\GlobalOptionsEventListener;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DrupalConfigInjector extends GlobalOptionsEventListener implements EventSubscriberInterface, ConfigAwareInterface, ContainerAwareInterface
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;

    public function __construct(protected FileSystem $drupalFileSystem, Application $application)
    {
        parent::__construct();
        $this->setApplication($application);
    }

    public function injectEnvironmentConfig(ConsoleCommandEvent $event): void
    {
        $this->addEnvironmentConfiguration($event);
    }

    public function onPostInvokeCommand(PostInvokeCommandEvent $event): void
    {
        $input = $event->getParentInput();
        $output = new NullOutput();
        $consoleEvent = new ConsoleCommandEvent(null, $input, $output);
        $this->addEnvironmentConfiguration($consoleEvent);
    }

    protected function addEnvironmentConfiguration(ConsoleCommandEvent $event): void
    {
        try {
            $this->drupalFileSystem->getDrupalRoot();
        } catch (\OutOfBoundsException $e) {
            // If Drupal root is not found, skip adding environment configuration.
            return;
        }
        /** @var PolymerConfig $config */
        $config = $this->getConfig();
        $input = $event->getInput();

        $globalOptions = $config->get($this->prefix, []);
        if ($config instanceof \Consolidation\Config\GlobalOptionDefaultValuesInterface) {
            $globalOptions += $config->getGlobalOptionDefaultValues();
        }

        $globalOptions += $this->applicationOptionDefaultValues();
        if (array_key_exists('environment', $globalOptions) && array_key_exists('site', $globalOptions)) {
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
            /** @var Config $siteConfig */
            $siteConfig = $config->getContext('site');
            /** @var Config $siteEnvironmentConfig */
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [];
//        return [
//            ConsoleEvents::COMMAND => [
//                ['injectEnvironmentConfig', 1],
//            ],
//            PolymerEvents::POST_INVOKE_COMMAND => [
//                ['onPostInvokeCommand'],
//            ],
//        ];
    }
}
