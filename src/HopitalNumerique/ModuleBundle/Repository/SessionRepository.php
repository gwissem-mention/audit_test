<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;

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
            ->leftJoin('ses.module','module')
            ->where( 'module.id = :idModule')
            ->setParameter('idModule', $condition->value )
            ->groupBy('ses.id')
            ->orderBy('ses.dateSession');

        return $qb;
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getAllDatasForGrid( $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ses')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
             ->leftJoin('ses.etat','refEtat')
             ->andWhere('refEtat.id = 403')
            // ->leftJoin('ses.module','module')
            ->groupBy('ses.id')
            ->orderBy('ses.dateSession');

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
