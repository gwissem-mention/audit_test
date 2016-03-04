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
    public function getAllDatasForGrid( $domainesIds, $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ses')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->leftJoin('ses.etat', 'refEtat')
                ->andWhere('refEtat.id = 403')
            ->leftJoin('ses.module', 'mod')
                ->leftJoin('mod.domaines', 'domaine')
                    ->andWhere($qb->expr()->orX(
                        $qb->expr()->in('domaine.id', ':domainesId'),
                        $qb->expr()->isNull('domaine.id')
                    ))
                ->setParameter('domainesId', $domainesIds)
            //->andWhere('ses.archiver = false')
            ->groupBy('ses.id', 'domaine.id')
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
    public function getSessionsForFormateur( $user, $withDate = false, $limit = false )
    {
        $qb = $this->_em->createQueryBuilder()
                        ->select('ses')
                        ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
                        ->leftJoin('ses.etat','refEtat')
                        ->andWhere('ses.formateur = :user', 'refEtat.id = 403')
                        ->setParameter('user', $user)
                        ->orderBy('ses.dateSession', 'DESC');

        if( $withDate !== false){
            if( $withDate == 'beforeToday' ){
                $qb->andWhere('ses.dateSession < :today')
                   ->setParameter('today', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME);
            }else{
                $qb->andWhere('ses.dateSession > :today')
                   ->setParameter('today', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME);
            }
        }

        if( $limit !== false ){
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Retourne la liste des sessions ou l'utilisateur doit/à participé pour le dashboard user
     *
     * @param idDomaine $idDomaine Domaine concerné
     * 
     * @return QueryBuilder
     */
    public function getSessionsInscriptionOuverteModuleDomaine($idDomaine)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ses')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->leftJoin('ses.etat','etat')
            ->leftJoin('ses.module','module')
            ->leftJoin('module.domaines', 'domaine')
            ->where('domaine.id = :idDomaine')
            ->andWhere('ses.dateOuvertureInscription <= :today')
            ->andWhere('ses.dateFermetureInscription >= :today')
            ->andWhere('ses.archiver != true')
            ->andWhere('etat.id = :idEtat')
            ->setParameters( array(
                'idDomaine' => $idDomaine,
                'today'     => new \DateTime(),
                'idEtat'    => 403
            ))
            ->groupBy('ses.id')
            ->orderBy('ses.dateSession', 'ASC');

        return $qb;
    }

    /**
     * Retourne la liste des sessions ou l'utilisateur doit/à participé pour le dashboard user
     *
     * @param User $user L'utilisateur concerné
     * 
     * @return QueryBuilder
     */
    public function getSessionsForDashboard( $user )
    {
        return $this->_em->createQueryBuilder()
                        ->select('ses.id, module.titre, refEtatParticipation.id as refEtatParticipationId, refEtatEvaluation.id as refEtatEvaluationId, ses.dateSession, module.id as moduleId')
                        ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
                        ->leftJoin('ses.inscriptions','inscription')
                        ->leftJoin('ses.module','module')
                        ->leftJoin('inscription.etatInscription','refEtatInscription')
                        ->leftJoin('inscription.etatParticipation','refEtatParticipation')
                        ->leftJoin('inscription.etatEvaluation','refEtatEvaluation')
                        ->andWhere('inscription.user = :user', 'refEtatInscription.id = 407')
                        ->setParameter('user', $user)
                        ->orderBy('ses.dateSession', 'DESC');
    }

    /**
     * Retourne les sessions des 15 prochains jours
     *
     * @return QueryBuilder
     */
    public function getNextSessions($domainesUser)
    {
        $today    = new \DateTime();

        return $this->_em->createQueryBuilder()
                        ->select('ses.id, ses.dateSession, count(ins) as inscriptions, mod.titre')
                        ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
                        ->leftJoin('ses.inscriptions', 'ins', 'WITH', 'ins.etatInscription = :etatInscription')
                        ->leftJoin('ses.module', 'mod')
                        ->leftJoin('mod.domaines', 'domaine')
                            ->where('domaine.id IN (:idDomaines)')
                        ->andWhere('ses.dateSession > :today', 'ses.etat = :idActif')
                        ->setParameters(array(
                            'idActif'         => 403,
                            'etatInscription' => 407,
                            'idDomaines'      => $domainesUser
                        ))
                        ->setParameter('today', $today, \Doctrine\DBAL\Types\Type::DATETIME)
                        ->setMaxResults(5)
                        ->groupBy('ses.id')
                        ->orderBy('ses.dateSession', 'ASC');
    }

    /**
     * Retourne les sessions à risque, càd n'ayant pas assez de participants pour des sessions prochaines.
     *
     * @param integer   $nombreParticipantsActuelMax Nombre maximum de particpants actuellement enregistrés
     * @param \DateTime $dateLimite                  Date limite
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function getSessionsRisquees($nombreParticipantsActuelMax, \DateTime $dateLimite)
    {
        $queryBuilder = $this->createQueryBuilder('session');

        $queryBuilder
            ->leftJoin('session.inscriptions', 'inscription')
            ->where($queryBuilder->expr()->between('session.dateSession', ':aujourdhui', ':dateLimite'))
            ->groupBy('session.id')
            ->having($queryBuilder->expr()->lte('COUNT(inscription.id)', ':nombreParticipantsActuelMax'))
            ->setParameters(array(
                'aujourdhui' => new \DateTime(),
                'dateLimite' => $dateLimite,
                'nombreParticipantsActuelMax' => $nombreParticipantsActuelMax
            ))
        ;

        return $queryBuilder;
    }
}
