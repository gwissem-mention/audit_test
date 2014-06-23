<?php

namespace HopitalNumerique\EtablissementBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * EtablissementRepository
 */
class EtablissementRepository extends EntityRepository
{
    /**
     * Récupère les données pour l'export CSV
     *
     * @return QueryBuilder
     */
    public function getDatasForExport( $ids )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('etab')
            ->from('HopitalNumeriqueEtablissementBundle:Etablissement', 'etab')
            ->where('etab.id IN (:ids)')
            ->orderBy('etab.nom')
            ->setParameter('ids', $ids);
            
        return $qb;
    }
}