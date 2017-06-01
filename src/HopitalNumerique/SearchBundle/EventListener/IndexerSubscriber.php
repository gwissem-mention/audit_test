<?php

namespace HopitalNumerique\SearchBundle\EventListener;

use FOS\ElasticaBundle\Event\TransformEvent;
use HopitalNumerique\ForumBundle\Entity\Post;
use HopitalNumerique\ForumBundle\Entity\Topic;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\PublicationBundle\Twig\PublicationExtension;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for Indexer related events
 */
class IndexerSubscriber implements EventSubscriberInterface
{
    /**
     * @var PublicationExtension
     */
    protected $publicationExtension;

    /**
     * IndexerSubscriber constructor.
     *
     * @param PublicationExtension $publicationExtension
     */
    public function __construct(PublicationExtension $publicationExtension)
    {
        $this->publicationExtension = $publicationExtension;
    }

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
        $document = $event->getDocument();

        if ($event->getObject() instanceof Objet || $event->getObject() instanceof Contenu) {
            if ($document->has('content')) {
                $document->set('content', strip_tags(
                    $this->publicationExtension->parsePublication($document->get('content'))
                ));
            }
        }

        if ($event->getObject() instanceof Topic) {
            /** @var Topic $topic */
            $topic = $event->getObject();
            $roles = array_intersect(
                $topic->getBoard()->getReadAuthorisedRoles(),
                $topic->getBoard()->getCategory()->getReadAuthorisedRoles(),
                $topic->getBoard()->getCategory()->getForum()->getReadAuthorisedRoles()
            );

            $document->set('authorised_roles', $roles);
        }

        if ($event->getObject() instanceof Post) {
            /** @var Post $post */
            $post = $event->getObject();
            $topic = $post->getTopic();
            $roles = array_intersect(
                $topic->getBoard()->getReadAuthorisedRoles(),
                $topic->getBoard()->getCategory()->getReadAuthorisedRoles(),
                $topic->getBoard()->getCategory()->getForum()->getReadAuthorisedRoles()
            );

            $document->set('authorised_roles', $roles);
        }
    }
}
