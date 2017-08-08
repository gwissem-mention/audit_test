<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

/**
 * RechercheParcoursDetailsRepository.
 */
class RechercheParcoursDetailsRepository extends EntityRepository
{
    /**
     * Compte le nombre de chapitres lié à l'outil.
     *
     * @return QueryBuilder
     */
    public function countDetails()
    {
        return $this->_em->createQueryBuilder()
            ->select('count(details)')
            ->from('\HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails', 'details')
        ;
    }
}
