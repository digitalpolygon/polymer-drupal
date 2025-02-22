<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Services\EventSubscriber;

use DigitalPolygon\Polymer\Robo\Event\CollectConfigContextsEvent;
use DigitalPolygon\Polymer\Robo\Event\PolymerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContextProvidersSubscriber implements EventSubscriberInterface
{
    public function addContexts(CollectConfigContextsEvent $event): void
    {
        $event->addPlaceholderContext('site');
        $event->addPlaceholderContext('site_environment');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        $events = [
            PolymerEvents::COLLECT_CONFIG_CONTEXTS => [
                ['addContexts', -1000]
            ],
        ];
        return $events;
    }
}
