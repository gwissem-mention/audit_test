<?php

namespace HopitalNumerique\PaiementBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RemboursementRepository
 */
class RemboursementRepository extends EntityRepository
{
    /**
     * Retourne la liste des remboursements ordonnées par région
     *
     * @return array
     */
    public function getRemboursementsOrdered()
    {
        return $this->_em->createQueryBuilder()
                         ->select('rem')
                         ->from('HopitalNumeriquePaiementBundle:Remboursement', 'rem')
                         ->leftJoin('rem.region', 'region')
                         ->orderBy('region.libelle','ASC');
    }
}
