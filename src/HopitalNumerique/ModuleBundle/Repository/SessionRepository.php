<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * SessionRepository
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('
                    ses.id,
                    ses.dateSession,
                    ses.dateOuvertureInscription,
                    ses.dateFermetureInscription,
                    ses.horaires,
                    refDuree.libelle as duree,
                    refEtat.libelle as etat,
                    count(inscriptions) as nbInscrits,
                    (ses.nombrePlaceDisponible - count(inscriptions)) as placeRestantes')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->leftJoin('ses.duree','refDuree')
            ->leftJoin('ses.etat','refEtat')
            ->leftJoin('ses.module','module')
            ->leftJoin('ses.inscriptions', 'inscriptions', Join::WITH, 'inscriptions.etatInscription = :idAcccepte')
            ->setParameter('idAcccepte', 332)
            ->where( 'module.id = :idModule')
            ->setParameter('idModule', $condition->value )
            ->orderBy('ses.dateSession');
    
        return $qb;
    }
}
