<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Plugin\Hooks;

use Consolidation\AnnotatedCommand\Attributes\Hook;
use DigitalPolygon\Polymer\Robo\Config\ConfigAwareTrait;
use DigitalPolygon\Polymer\Robo\Config\PolymerConfig;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Common\IO;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class SiteConfigHook implements ConfigAwareInterface, LoggerAwareInterface, IOAwareInterface, ContainerAwareInterface
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;
    use IO;
    use LoggerAwareTrait;

    #[Hook(type: 'pre-command-event', target: '*')]
    public function setSiteConfig(ConsoleCommandEvent $event): void
    {
        $commandInput = $event->getInput();
        $instanceInput = $this->input();
        /** @var PolymerConfig $config */
        $config = $this->getConfig();
        $x = 5;
//        $siteContext = $config->getContext('site');
//        $siteEnvironmentContext = $config->getContext('site_environment');
//        $options = $this->input()->getOptions();
//        $site = $options['site'] ?? 'default';
//        $environment = $options['environment'] ?? 'local';
//        $docroot = $this->getContainer()->get('drupalFileSystem')->getDrupalRoot();
//        $siteDirPath = $docroot . '/sites/' . $site;
//        $siteConfigPath = $siteDirPath . '/polymer.yml';
//        $siteEnvironmentConfigPath = $siteDirPath . '/' . $environment . '.polymer.yml';
//        $loader = new YamlConfigLoader();
//        $siteConfig = $loader->load($siteConfigPath)->export();
//        $loader = new YamlConfigLoader();
//        $siteEnvironmentConfig = $loader->load($siteEnvironmentConfigPath)->export();
//        $siteContext->replace($siteConfig);
//        $siteEnvironmentContext->replace($siteEnvironmentConfig);
//        $config->reprocess();
    }
}
