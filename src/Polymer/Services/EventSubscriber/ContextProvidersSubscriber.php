<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Services\EventSubscriber;

use Consolidation\Config\Loader\YamlConfigLoader;
use DigitalPolygon\Polymer\Robo\Event\CollectConfigContextsEvent;
use DigitalPolygon\PolymerDrupal\Polymer\Services\FileSystem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContextProvidersSubscriber implements EventSubscriberInterface
{
    public function __construct(protected FileSystem $drupalFileSystem)
    {
    }

    public function addContexts(CollectConfigContextsEvent $event): void
    {
        try {
            $this->drupalFileSystem->getDrupalRoot();
        } catch (\OutOfBoundsException $e) {
            // If Drupal root is not found, skip adding environment configuration.
            return;
        }
        $site = $event->getInput()->getOption('site');
        $environment = $event->getInput()->getOption('environment');
        $drupalConfig = [];
        $possibleConfigFiles = [];
        if (is_string($site) && in_array($site, $this->drupalFileSystem->getMultisiteDirs())) {
            $sitePath = $this->drupalFileSystem->getDrupalRoot() . '/sites/' . $site;
            $possibleConfigFiles['site'] =  $sitePath . '/polymer.yml';
            if (is_string($environment)) {
                $possibleConfigFiles['site_environment'] = $sitePath . '/' . $environment . '.polymer.yml';
            }
        }
        $possibleConfigFiles = array_filter($possibleConfigFiles, function ($file) {
            return file_exists($file);
        });
        foreach ($possibleConfigFiles as $configId => $file) {
            $loader = new YamlConfigLoader();
            $drupalConfig[$configId] = $loader->load($file)->export();
        }
        $event->addContexts($drupalConfig);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        $events = [
            CollectConfigContextsEvent::class => [
                ['addContexts', -1000]
            ],
        ];
        return $events;
    }
}
