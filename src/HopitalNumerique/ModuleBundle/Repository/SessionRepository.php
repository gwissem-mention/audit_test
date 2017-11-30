<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ModuleBundle\Entity\Module;
use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * SessionRepository.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @return QueryBuilder
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid($condition)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ses')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->leftJoin('ses.module', 'module')
            ->where('module.id = :idModule')
            ->setParameter('idModule', $condition->value)
            ->groupBy('ses.id')
            ->orderBy('ses.dateSession');

        return $qb;
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @param $domainesIds
     * @param $condition
     *
     * @return QueryBuilder
     *
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getAllDatasForGrid($domainesIds, $condition)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ses', 'form')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->join('ses.formateur', 'form')
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
     * Retourne la liste des sessions du formateur.
     *
     * @param      $user
     * @param bool $withDate
     * @param bool $limit
     *
     * @return array
     */
    public function getSessionsForFormateur($user, $withDate = false, $limit = false)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('ses')
            ->addSelect('refEtat', 'formateur', 'module', 'inscriptions', 'user')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->leftJoin('ses.etat', 'refEtat')
            ->leftJoin('ses.formateur', 'formateur')
            ->leftJoin('ses.module', 'module')
            ->leftJoin('ses.inscriptions', 'inscriptions')
            ->leftJoin('inscriptions.user', 'user')
            ->andWhere('formateur = :user', 'refEtat.id = :activeSessionId')
            ->setParameters([
                'user' => $user,
                'activeSessionId' => Reference::STATUT_SESSION_ACTIVE_ID
            ])
            ->orderBy('ses.dateSession', 'DESC')
        ;

        if ($withDate !== false) {
            if ($withDate == 'beforeToday') {
                $qb->andWhere('ses.dateSession < :today')
                   ->setParameter('today', new \DateTime(), Type::DATETIME);
            } else {
                $qb->andWhere('ses.dateSession > :today')
                   ->setParameter('today', new \DateTime(), Type::DATETIME);
            }
        }

        if ($limit !== false) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne la liste des sessions ou l'utilisateur doit/à participé pour le dashboard user.
     *
     * @param $idDomaine $idDomaine Domaine concerné
     *
     * @return QueryBuilder
     */
    public function getSessionsInscriptionOuverteModuleDomaine($idDomaine)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ses')
            ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
            ->leftJoin('ses.etat', 'etat')
            ->leftJoin('ses.module', 'module')
            ->leftJoin('module.domaines', 'domaine')
            ->where('domaine.id = :idDomaine')
            ->andWhere('ses.dateOuvertureInscription <= :today')
            ->andWhere('ses.dateFermetureInscription >= :today')
            ->andWhere('ses.archiver != true')
            ->andWhere('etat.id = :idEtat')
            ->setParameters([
                'idDomaine' => $idDomaine,
                'today' => new \DateTime(),
                'idEtat' => 403,
            ])
            ->groupBy('ses.id')
            ->orderBy('ses.dateSession', 'ASC');

        return $qb;
    }

    /**
     * Retourne la liste des sessions ou l'utilisateur doit/à participé pour le dashboard user.
     *
     * @param User $user L'utilisateur concerné
     *
     * @return QueryBuilder
     */
    public function getSessionsForDashboard($user)
    {
        return $this->_em->createQueryBuilder()
                        ->select('ses.id, module.titre, refEtatParticipation.id as refEtatParticipationId, refEtatEvaluation.id as refEtatEvaluationId, ses.dateSession, module.id as moduleId')
                        ->from('HopitalNumeriqueModuleBundle:Session', 'ses')
                        ->leftJoin('ses.inscriptions', 'inscription')
                        ->leftJoin('ses.module', 'module')
                        ->leftJoin('inscription.etatInscription', 'refEtatInscription')
                        ->leftJoin('inscription.etatParticipation', 'refEtatParticipation')
                        ->leftJoin('inscription.etatEvaluation', 'refEtatEvaluation')
                        ->andWhere('inscription.user = :user', 'refEtatInscription.id = 407')
                        ->setParameter('user', $user)
                        ->orderBy('ses.dateSession', 'DESC');
    }

    /**
     * Retourne les $limit prochaines sessions.
     *
     * @param Domaine[] $domains
     * @param int $limit
     *
     * @return ArrayCollection|Session[]
     */
    public function getNextSessionsByDomains($domains, $limit = 5)
    {
        $today = new \DateTime();

        $queryBuilder = $this->createQueryBuilder('session');
        $queryBuilder
            ->select(
                'session.id',
                'session.dateSession',
                'count(inscription) as inscriptions',
                'module.titre'
            )
            ->leftJoin(
                'session.inscriptions',
                'inscription',
                Join::WITH,
                $queryBuilder->expr()->eq('inscription.etatInscription', Reference::STATUT_SESSION_ACCEPTED_ID)
            )
            ->join('session.module', 'module')
            ->join(
                'module.domaines',
                'domaine',
                Join::WITH,
                $queryBuilder->expr()->in(
                    'domaine',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )
            ->andWhere('session.dateSession > :today', 'session.etat = :idActif')
            ->groupBy('session.id')
            ->orderBy('session.dateSession', 'ASC')
            ->setMaxResults($limit)
            ->setParameters([
                'idActif' => Reference::STATUT_SESSION_ACTIVE_ID,
                'today' => $today
            ])
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Retourne les sessions à risque, càd n'ayant pas assez de participants pour des sessions prochaines.
     *
     * @param int       $nombreParticipantsActuelMax Nombre maximum de particpants actuellement enregistrés
     * @param \DateTime $dateLimite                  Date limite
     *
     * @return QueryBuilder QueryBuilder
     */
    public function getSessionsRisquees($nombreParticipantsActuelMax, \DateTime $dateLimite)
    {
        $queryBuilder = $this->createQueryBuilder('session');

        $queryBuilder
            ->leftJoin('session.inscriptions', 'inscription')
            ->where($queryBuilder->expr()->between('session.dateSession', ':aujourdhui', ':dateLimite'))
            ->andWhere('session.archiver = false')
            ->andWhere('session.etat = :activeStatus')
            ->groupBy('session.id')
            ->having($queryBuilder->expr()->lte('COUNT(inscription.id)', ':nombreParticipantsActuelMax'))
            ->setParameters([
                'aujourdhui' => new \DateTime(),
                'dateLimite' => $dateLimite,
                'nombreParticipantsActuelMax' => $nombreParticipantsActuelMax,
                'activeStatus' => Reference::STATUT_SESSION_ACTIVE_ID,
            ])
        ;

        return $queryBuilder;
    }

    /**
     * Returns coming training sessions for module starting between today and maxDate.
     *
     * @param Module $module
     * @param $maxDate
     *
     * @return Session[]
     */
    public function getComingSessionsForModule($module, $maxDate)
    {
        return $this->createQueryBuilder('session')
            ->select('session', 'session')
            ->andWhere(
                'session.module = :module',
                'session.dateSession > :today',
                'session.dateSession < :maxDate',
                'session.etat = :sessionState'
            )
            ->orderBy('session.dateSession', 'ASC')
            ->setParameters([
                'module' => $module,
                'today' => new \DateTime(),
                'maxDate' => $maxDate,
                'sessionState' => Reference::STATUT_SESSION_ACTIVE_ID,
            ])
            ->getQuery()->getResult()
        ;
    }
}
