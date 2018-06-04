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
use HopitalNumerique\ModuleBundle\Entity\SessionStatus;
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
        return $this->createQueryBuilder('session')
            ->leftJoin('session.module', 'module')
            ->where('module.id = :idModule')
            ->setParameter('idModule', $condition->value)
            ->groupBy('session.id')
            ->orderBy('session.dateSession');
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
        $queryBuilder = $this->createQueryBuilder('session');

        $queryBuilder
            ->join('session.formateur', 'form')
            ->leftJoin('session.module', 'mod')
            ->leftJoin('mod.domaines', 'domaine')
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->in('domaine.id', ':domainesId'),
                $queryBuilder->expr()->isNull('domaine.id')
            ))
            ->setParameter('domainesId', $domainesIds)
            ->groupBy('session.id', 'domaine.id')
            ->orderBy('session.dateSession');

        return $queryBuilder;
    }

    /**
     * Retourne la liste des sessions du formateur.
     *
     * @param User $user
     * @param Domaine $domain
     * @param bool $withDate
     * @param bool $limit
     *
     * @return array
     */
    public function getSessionsForFormateur(User $user, Domaine $domain = null, $withDate = false, $limit = false)
    {
        $queryBuilder = $this->createQueryBuilder('session')
            ->addSelect('refEtat', 'formateur', 'module', 'inscriptions', 'user')
            ->join('session.etat', 'refEtat', Join::WITH, 'refEtat.id = :activeSessionId')
            ->join('session.formateur', 'formateur', Join::WITH, 'formateur = :user')
            ->join('session.module', 'module')
            ->leftJoin('session.inscriptions', 'inscriptions')
            ->leftJoin('inscriptions.user', 'user')
            ->setParameters([
                'user' => $user,
                'activeSessionId' => SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID,
            ])
            ->orderBy('session.dateSession', 'DESC')
        ;

        if (null !== $domain) {
            $queryBuilder
                ->join('module.domaines', 'domain', Join::WITH, 'domain.id = :domainId')
                ->setParameter('domainId', $domain->getId())
            ;
        }

        if ($withDate !== false) {
            if ($withDate == 'beforeToday') {
                $queryBuilder->andWhere('session.dateSession < :today')
                   ->setParameter('today', new \DateTime(), Type::DATETIME);
            } else {
                $queryBuilder->andWhere('session.dateSession > :today')
                   ->setParameter('today', new \DateTime(), Type::DATETIME);
            }
        }

        if ($limit !== false) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
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
        return $this->createQueryBuilder('session')
            ->leftJoin('session.etat', 'etat')
            ->leftJoin('session.module', 'module')
            ->leftJoin('module.domaines', 'domaine')
            ->where('domaine.id = :idDomaine')
            ->andWhere('session.dateOuvertureInscription <= :today')
            ->andWhere('session.dateFermetureInscription >= :today')
            ->andWhere('session.archiver != true')
            ->andWhere('etat.id = :activeStatusId')
            ->setParameters([
                'idDomaine' => $idDomaine,
                'today' => new \DateTime(),
                'activeStatusId' => SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID,
            ])
            ->groupBy('session.id')
            ->orderBy('session.dateSession', 'ASC');
    }

    /**
     * Retourne la liste des sessions ou l'utilisateur doit/à participé pour le dashboard user.
     *
     * @param User $user L'utilisateur concerné
     * @param Domaine $domain
     *
     * @return QueryBuilder
     */
    public function getSessionsForDashboard(User $user, Domaine $domain)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('session.id, module.titre, refEtatParticipation.id as refEtatParticipationId, refEtatEvaluation.id as refEtatEvaluationId, session.dateSession, module.id as moduleId')
            ->from('HopitalNumeriqueModuleBundle:Session', 'session')
            ->join('session.inscriptions', 'inscription', Join::WITH, 'inscription.user = :user')
            ->join('inscription.etatInscription', 'refEtatInscription', Join::WITH, 'refEtatInscription.id = :acceptedStatusId')
            ->join('session.module', 'module')
            ->leftJoin('inscription.etatParticipation', 'refEtatParticipation')
            ->leftJoin('inscription.etatEvaluation', 'refEtatEvaluation')
            ->setParameters([
                'user' => $user,
                'acceptedStatusId' => SessionStatus::STATUT_FORMATION_ACCEPTED_ID,
            ])
            ->orderBy('session.dateSession', 'DESC')
        ;

        if (null !== $domain) {
            $queryBuilder
                ->join('module.domaines', 'domain', Join::WITH, 'domain.id = :domainId')
                ->setParameter('domainId', $domain->getId())
            ;
        }

        return $queryBuilder;
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
                $queryBuilder->expr()->eq('inscription.etatInscription', SessionStatus::STATUT_FORMATION_ACCEPTED_ID)
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
                'idActif' => SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID,
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
                'activeStatus' => SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID,
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
                'sessionState' => SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID,
            ])
            ->getQuery()->getResult()
        ;
    }
}
