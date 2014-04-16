<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * SessionRepository
 */
class SessionRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid( $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ses.id, ses.dateSession, ses.dateOuvertureInscription, ses.dateFermetureInscription, ses.horaires, refDuree.libelle as duree, refEtat.libelle as etat')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->leftJoin('ses.duree','refDuree')
            ->leftJoin('ses.etat','refEtat')
            ->leftJoin('ses.module','module')
            ->where( 'module.id = :idModule')
            ->setParameter('idModule', $condition->value )
            ->orderBy('ses.dateSession');
    
        return $qb;
    }
}
