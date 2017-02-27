<?php

namespace HopitalNumerique\SearchBundle\EventListener;

use FOS\ElasticaBundle\Event\TransformEvent;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for Indexer related events
 */
class IndexerSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TransformEvent::POST_TRANSFORM => [
                'postTransform',
            ],
        ];
    }

    /**
     * Do some post transform actions
     *
     * @param TransformEvent $event
     */
    public function postTransform(TransformEvent $event)
    {
        if ($event->getObject() instanceof Objet || $event->getObject() instanceof Contenu) {
            $document = $event->getDocument();
            if ($document->has('content')) {
                $document->set('content', strip_tags($document->get('content')));
            }
        }
    }
}
