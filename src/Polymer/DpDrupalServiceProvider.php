<?php

namespace DigitalPolygon\PolymerDrupal\Polymer;

use DigitalPolygon\PolymerDrupal\Polymer\Services\ContextProvidersSubscriber;
use DigitalPolygon\PolymerDrupal\Polymer\Services\EventSubscriber\DrupalConfigInjector;
use DigitalPolygon\PolymerDrupal\Polymer\Services\FileSystem;
use DrupalFinder\DrupalFinderComposerRuntime;
use League\Container\Argument\ResolvableArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Symfony\Component\Console\Input\InputOption;

class DpDrupalServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function provides(string $id): bool
    {
        $services = [
            'drupalConfigContextProvider',
            'drupalConfigInjector',
            'drupalFinder',
            'drupalFileSystem',
        ];
        return in_array($id, $services);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->getContainer()->addShared('drupalFinder', DrupalFinderComposerRuntime::class);
        $this->getContainer()->addShared('drupalConfigContextProvider', ContextProvidersSubscriber::class);
        $this->getContainer()->addShared('drupalConfigInjector', DrupalConfigInjector::class)
            ->addArgument(new ResolvableArgument('drupalFileSystem'))
            ->addArgument(new ResolvableArgument('application'));
        $this->getContainer()->addShared('drupalFileSystem', FileSystem::class)
            ->addArgument(new ResolvableArgument('drupalFinder'));
    }

    public function boot(): void
    {
        $this->getContainer()->extend('eventDispatcher')
            ->addMethodCall('addSubscriber', ['drupalConfigContextProvider'])
            ->addMethodCall('addSubscriber', ['drupalConfigInjector']);

        $this->getContainer()->extend('application')
            ->addMethodCall('addGlobalOption', [
                new InputOption('--site', NULL, InputOption::VALUE_REQUIRED, 'The multisite to execute this command against.', 'default')
            ]);
    }
}
