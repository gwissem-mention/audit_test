<?php

namespace HopitalNumerique\PaiementBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FactureRepository
 */
class FactureRepository extends EntityRepository
{
    /**
     * Retourne la liste des factures ordonnées par date
     *
     * @return QueryBuilder
     */
    public function getFacturesOrdered( $user )
    {
        return $this->_em->createQueryBuilder()
                         ->select('fac')
                         ->from('HopitalNumeriquePaiementBundle:Facture', 'fac')
                         ->where('fac.user = :user')
                         ->setParameter('user', $user)
                         ->orderBy('fac.dateCreation','DESC');
    }
}
