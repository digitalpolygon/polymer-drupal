<?php

namespace DigitalPolygon\Polymer\polymer_drupal\Services\EventSubscriber;

use DigitalPolygon\Polymer\Core\Robo\Config\ConfigAwareTrait;
use DigitalPolygon\Polymer\Core\Robo\Event\PolymerEvents;
use DigitalPolygon\Polymer\Core\Robo\Event\PostInvokeCommandEvent;
use Robo\Contract\ConfigAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PostInvokeCommandSubscriber implements EventSubscriberInterface, ConfigAwareInterface
{
    use ConfigAwareTrait;

    public function onPostInvokeCommand(PostInvokeCommandEvent $event): void
    {
        $newSite = $event->getNewInput()->getOption('site');
        $parentSite = $event->getParentInput()->getOption('site');
        $x = 5;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PolymerEvents::POST_INVOKE_COMMAND => 'onPostInvokeCommand',
        ];
    }
}
