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
        $qb->select('ses')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->leftJoin('ses.duree','refDuree')
            ->leftJoin('ses.etat','refEtat')
            ->leftJoin('ses.module','module')
            ->where( 'module.id = :idModule')
            ->setParameter('idModule', $condition->value )
            ->groupBy('ses.id')
            ->orderBy('ses.dateSession')
            ;
        
        // $qb->select('
        //             ses.id,
        //             ses.dateSession,
        //             ses.dateOuvertureInscription,
        //             ses.dateFermetureInscription,
        //             ses.horaires,
        //             refDuree.libelle as duree,
        //             refEtat.libelle as etat,
        //             count(inscriptions) as nbInscrits,
        //             count(inscriptionsEnAttente) as nbInscritsEnAttente,
        //             (ses.nombrePlaceDisponible - count(inscriptions)) as placeRestantes')
        //     ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
        //     ->leftJoin('ses.duree','refDuree')
        //     ->leftJoin('ses.etat','refEtat')
        //     ->leftJoin('ses.module','module')
        //     ->leftJoin('ses.inscriptions', 'inscriptions', Join::WITH, 'inscriptions.etatInscription = :idAcccepte')
        //     ->setParameter('idAcccepte', 407)
        //     ->leftJoin('ses.inscriptions', 'inscriptionsEnAttente', Join::WITH, 'inscriptionsEnAttente.etatInscription = :idAttente')
        //     ->setParameter('idAttente', 406)
        //     ->where( 'module.id = :idModule')
        //     ->setParameter('idModule', $condition->value )
        //     ->groupBy('ses.id')
        //     ->orderBy('ses.dateSession')
        //     ;
    

        return $qb;
    }

    /**
     * Retourne la liste des sessions du formateur
     *
     * @param User $user L'utilisateur concerné
     * 
     * @return QueryBuilder
     */
    public function getSessionsForFormateur( $user )
    {
        return $this->_em->createQueryBuilder()
                         ->select('ses')
                         ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
                         ->leftJoin('ses.etat','refEtat')
                         ->andWhere('ses.formateur = :user', 'refEtat.id = 403')
                         ->andWhere('ses.dateSession < :today')
                         ->setParameter('user', $user)
                         ->setParameter('today', new \DateTime() );
    }
}
