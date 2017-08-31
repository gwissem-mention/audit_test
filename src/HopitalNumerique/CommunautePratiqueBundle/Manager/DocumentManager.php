<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Manager;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use HopitalNumerique\CommunautePratiqueBundle\Event\Group\DocumentCreatedEvent;

/**
 * Manager de Document.
 */
class DocumentManager extends \Nodevo\ToolsBundle\Manager\Manager
{
    protected $class = 'HopitalNumerique\CommunautePratiqueBundle\Entity\Document';

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * DocumentManager constructor.
     *
     * @param EntityManager            $em
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($em);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if (null === $orderBy) {
            $orderBy = ['libelle' => 'ASC'];
        }

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param $entity
     */
    public function save($entity)
    {
        /** @var Document $entity */
        parent::save($entity);

        /**
         * Fire 'GROUP_DOCUMENT_CREATED' event
         */
        $event = new DocumentCreatedEvent($entity->getGroupe(), $entity);
        $this->eventDispatcher->dispatch(Events::GROUP_DOCUMENT_CREATED, $event);
    }
}
