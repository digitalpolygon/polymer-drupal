<?php

namespace DigitalPolygon\PolymerDrupal\Polymer\Services;

use DigitalPolygon\Polymer\Robo\Event\CollectConfigContextsEvent;
use DigitalPolygon\Polymer\Robo\Event\PolymerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContextProvidersSubscriber implements EventSubscriberInterface
{
    public function addContexts(CollectConfigContextsEvent $event)
    {
        $event->addPlaceholderContext('site');
        $event->addPlaceholderContext('site_environment');
    }

    public static function getSubscribedEvents()
    {
        $events = [
            PolymerEvents::COLLECT_CONFIG_CONTEXTS => [
                ['addContexts', -1000]
            ],
        ];
        return $events;
    }
}
