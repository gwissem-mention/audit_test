<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursHistory;
use HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursHistoryRepository;
use HopitalNumerique\UserBundle\Entity\User;

class GuidedSearchHistoryWriter
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var RechercheParcoursHistoryRepository $historyRepository
     */
    protected $historyRepository;

    /**
     * GuidedSearchRetriever constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RechercheParcoursHistoryRepository $historyRepository
     */
    public function __construct(EntityManagerInterface $entityManager, RechercheParcoursHistoryRepository $historyRepository)
    {
        $this->entityManager = $entityManager;
        $this->historyRepository = $historyRepository;
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
    }

    public function lastNotification(RechercheParcoursGestion $parcoursGestion)
    {
        return $this->historyRepository->getNewest($parcoursGestion);
    }
}
