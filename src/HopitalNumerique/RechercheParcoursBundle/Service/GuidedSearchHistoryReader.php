<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursHistory;
use HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursHistoryRepository;

class GuidedSearchHistoryReader
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var RechercheParcoursHistoryRepository $historyRepository
     */
    protected $historyRepository;

    /**
     * GuidedSearchHistoryReader constructor.
     * @param ObjectManager $om
     * @param RechercheParcoursHistoryRepository $historyRepository
     */
    public function __construct(ObjectManager $om, RechercheParcoursHistoryRepository $historyRepository)
    {
        $this->manager = $om;
        $this->historyRepository = $historyRepository;
    }

    /**
     * Retrieves guided search history (all types)
     *
     * @param RechercheParcoursGestion $parcoursGestion
     *
     * @return array
     */
    public function getHistory(RechercheParcoursGestion $parcoursGestion)
    {
        return $this->manager->getRepository(RechercheParcoursHistory::class)->getHistory($parcoursGestion);
    }

    public function lastNotification(RechercheParcoursGestion $parcoursGestion)
    {
        return $this->historyRepository->getNewest($parcoursGestion);
    }
}
