<?php

namespace DigitalPolygon\PolymerDrupal;

use DigitalPolygon\Polymer\Robo\ConsoleApplication;
use DigitalPolygon\PolymerDrupal\Polymer\Services\ContextProvidersSubscriber;
use DigitalPolygon\PolymerDrupal\Services\EventSubscriber\DrupalConfigInjector;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Symfony\Component\Console\Input\InputOption;

class PolymerDrupalServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function provides(string $id): bool
    {
        $services = [
            'drupalConfigContextProvider'
        ];
        return in_array($id, $services);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->getContainer()->addShared('drupalConfigContextProvider', ContextProvidersSubscriber::class);
        $this->getContainer()->addShared('drupalConfigInjector', DrupalConfigInjector::class);

        $this->getContainer()->extend('eventDispatcher')
            ->addMethodCall('addSubscriber', ['drupalConfigContextProvider'])
            ->addMethodCall('addSubscriber', ['drupalConfigInjector']);

        $this->getContainer()->extend('application')
            ->addMethodCall('addGlobalOption', [
                new InputOption('--site', NULL, InputOption::VALUE_REQUIRED, 'The multisite to execute this command against.', 'default')
            ]);
    }
}
