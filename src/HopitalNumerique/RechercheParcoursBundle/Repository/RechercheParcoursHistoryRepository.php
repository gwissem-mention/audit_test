<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursHistory;

/**
 * RechercheParcoursHistoryRepository.
 */
class RechercheParcoursHistoryRepository extends EntityRepository
{
    /**
     * @param RechercheParcoursGestion $parcoursGestion
     *
     * @return array
     */
    public function getHistory(RechercheParcoursGestion $parcoursGestion)
    {
        $qb = $this->createQueryBuilder('history');
        $qb
            ->where('history.parcoursGestion = :parcours_gestion')
            ->orderBy('history.dateTime', 'desc')
            ->setParameters([
                'parcours_gestion' => $parcoursGestion->getId(),
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getNewest(RechercheParcoursGestion $parcoursGestion)
    {
        /**
         * @var $last RechercheParcoursHistory
         */
        $last = $this->findOneBy(
            ['parcoursGestion' => $parcoursGestion],
            ['dateTime' => 'DESC']
        );

        return $last ? $last->getDateTime() : null;
    }
}
