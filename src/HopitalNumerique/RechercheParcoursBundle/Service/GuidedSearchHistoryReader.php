<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursHistory;

class GuidedSearchHistoryReader
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    public function __construct(ObjectManager $om)
    {
        $this->manager = $om;
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
}
