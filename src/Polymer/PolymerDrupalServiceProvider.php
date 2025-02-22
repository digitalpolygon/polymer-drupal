<?php

namespace DigitalPolygon\PolymerDrupal\Polymer;

use DigitalPolygon\PolymerDrupal\Polymer\Services\EventSubscriber\ContextProvidersSubscriber;
use DigitalPolygon\PolymerDrupal\Polymer\Services\EventSubscriber\DrupalConfigInjector;
use DigitalPolygon\PolymerDrupal\Polymer\Services\EventSubscriber\PostInvokeCommandSubscriber;
use DigitalPolygon\PolymerDrupal\Polymer\Services\FileSystem;
use DrupalFinder\DrupalFinderComposerRuntime;
use League\Container\Argument\ResolvableArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Symfony\Component\Console\Input\InputOption;

class PolymerDrupalServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
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
            'drupalPostInvokeCommandSubscriber',
        ];
        return in_array($id, $services);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $container = $this->getContainer();
        $container->addShared('drupalConfigContextProvider', ContextProvidersSubscriber::class);
        $container->addShared('drupalConfigInjector', DrupalConfigInjector::class)
            ->addArgument(new ResolvableArgument('drupalFileSystem'))
            ->addArgument(new ResolvableArgument('application'));
        $container->addShared('drupalFinder', DrupalFinderComposerRuntime::class);
        $container->addShared('drupalFileSystem', FileSystem::class)
            ->addArgument(new ResolvableArgument('drupalFinder'));
    }

    public function boot(): void
    {
        $container = $this->getContainer();
        $container->addShared('drupalPostInvokeCommandSubscriber', PostInvokeCommandSubscriber::class);
        $container->extend('eventDispatcher')
            ->addMethodCall('addSubscriber', ['drupalConfigContextProvider'])
            ->addMethodCall('addSubscriber', ['drupalConfigInjector'])
            ->addMethodCall('addSubscriber', ['drupalPostInvokeCommandSubscriber']);

        $container->extend('application')
            ->addMethodCall('addGlobalOption', [
                new InputOption('--site', null, InputOption::VALUE_REQUIRED, 'The multisite to execute this command against.', 'default')
            ]);
    }
}
