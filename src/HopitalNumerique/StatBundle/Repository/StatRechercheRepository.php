<?php

namespace HopitalNumerique\StatBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

class StatRechercheRepository extends EntityRepository
{
    /**
     * Returns StatRecherche objects that have the current reference in their request
     * (and do not have the target reference)
     *
     * @param Reference $currentReference
     * @param Reference $targetReference
     *
     * @return array
     */
    public function findSearchHistoryByReferences(Reference $currentReference, Reference $targetReference = null)
    {
        $qb = $this->createQueryBuilder('stat_recherche');

        $qb
            ->andWhere('stat_recherche.requete LIKE :currentRefId')
            ->setParameter('currentRefId', '%\"' . $currentReference->getId() . '\"%')
        ;

        if (!is_null($targetReference)) {
            $qb
                ->andWhere('stat_recherche.requete NOT LIKE :targetRefId')
                ->setParameter('targetRefId', '%\"' . $targetReference->getId() . '\"%')
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
