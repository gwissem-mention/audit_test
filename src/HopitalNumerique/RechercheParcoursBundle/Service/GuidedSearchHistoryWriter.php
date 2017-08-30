<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursHistory;
use HopitalNumerique\RechercheParcoursBundle\Events;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\RechercheParcoursBundle\Event\GuidedSearchUpdatedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GuidedSearchHistoryWriter
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * GuidedSearchRetriever constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Creates a new history line for 'rechercheParcoursGestion'
     *
     * @param RechercheParcoursGestion $parcoursGestion
     * @param User $user
     * @param integer $notify
     * @param string $reason
     */
    public function create(RechercheParcoursGestion $parcoursGestion, User $user, $notify, $reason = '')
    {
        $history = new RechercheParcoursHistory();
        $history->setParcoursGestion($parcoursGestion);
        $history->setUserName($user->getNomPrenom());
        $history->setNotify($notify);
        $history->setReason($reason);
        $this->entityManager->persist($history);
        $this->entityManager->flush();

        if ('1' === $notify) {
            /**
             * Fire 'GUIDED_SEARCH_UPDATED' event
             */
            $event = new GuidedSearchUpdatedEvent($parcoursGestion, $reason);
            $this->eventDispatcher->dispatch(Events::GUIDED_SEARCH_UPDATED, $event);
        }
    }
}
