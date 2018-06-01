<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ModuleBundle\Entity\Session;
use HopitalNumerique\ModuleBundle\Entity\SessionStatus;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use Nodevo\RoleBundle\Entity\Role;

/**
 * InscriptionRepository.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionRepository extends EntityRepository
{
    /** @var array $adminGridSessionStatus */
    private $adminGridSessionStatus;

    /**
     * @param $sessionStatus
     */
    public function setAdminGridSessionStatus($sessionStatus)
    {
        $this->adminGridSessionStatus = $sessionStatus;
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @param \StdClass $condition
     *
     * @return QueryBuilder
     */
    public function getDatasForGrid(\StdClass $condition)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('insc.id,
                     user.id as userId,
                     user.lastname as userNom,
                     user.firstname as userPrenom,
                     user.roles,
                     session.id as sessionId,
                     refProfileType.libelle as userProfil,
                     refRegion.libelle as userRegion,
                     insc.commentaire,
                     refEtatInscription.libelle as etatInscription,
                     refEtatParticipation.libelle as etatParticipation,
                     refEtatEvaluation.libelle as etatEvaluation')
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
            ->leftJoin('insc.user', 'user')
            ->leftJoin('user.region', 'refRegion')
            ->leftJoin('user.profileType', 'refProfileType')
            ->leftJoin('insc.etatInscription', 'refEtatInscription')
            ->leftJoin('insc.etatParticipation', 'refEtatParticipation')
            ->leftJoin('insc.etatEvaluation', 'refEtatEvaluation')
            ->leftJoin('insc.session', 'session')
            ->where('session.id = :idSession')
            ->setParameter('idSession', $condition->value)
            ->orderBy('user.lastname');

        return $qb;
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @return QueryBuilder
     */
    public function getAllDatasForGrid($domainesIds, $condition)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ins')
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'ins')
            ->leftJoin('ins.session', 'ses')
                ->leftJoin('ses.module', 'mod')
                    ->leftJoin('mod.domaines', 'domaine')
                        ->andWhere($qb->expr()->orX(
                            $qb->expr()->in('domaine.id', ':domainesId'),
                            $qb->expr()->isNull('domaine.id')
                        ))
                    ->setParameter('domainesId', $domainesIds)
            ->join('ses.etat', 'refEtat', Join::WITH, 'refEtat.id = :activeStatusId')
            ->setParameter('activeStatusId', SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID)
            ->leftJoin('ins.user', 'user')
            ->groupBy('ins.id', 'domaine.id')
            ->orderBy('user.lastname');

        return $qb;
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur pour la création des factures.
     *
     * @param null $user
     *
     * @return QueryBuilder
     */
    public function getForFactures($user = null)
    {
        /** @var User $user */
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('insc')
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
            ->join('insc.etatEvaluation', 'refEvaluation', Join::WITH, 'refEvaluation.id = 29')
            ->join('insc.etatParticipation', 'refParticipation', Join::WITH, 'refParticipation.id = :okStatusId')
            ->leftJoin('insc.session', 'session')
            ->andWhere($qb->expr()->orX('insc.etatRemboursement != 6', $qb->expr()->isNull('insc.etatRemboursement')))
            ->andWhere('insc.facture IS NULL')
            ->setParameter('okStatusId', SessionStatus::STATUT_PARTICIPATION_OK_ID)
            ->orderBy('session.dateSession')
        ;

        if (!is_null($user)) {
            $qb->andWhere('insc.user = :user')
               ->setParameter('user', $user);

            if ($user->isRegionDom()) {
                $qb->leftJoin('session.module', 'module')
                    ->andWhere('module.id IS NULL')
                ;
            }
        }

        return $qb;
    }

    /**
     * Retourne la liste des inscriptions d'une facture ordonnée par dateSession.
     *
     * @param $facture
     *
     * @return QueryBuilder
     */
    public function getInscriptionsForFactureOrdered($facture)
    {
        return $this->_em->createQueryBuilder()
                         ->select('insc')
                         ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
                         ->leftJoin('insc.session', 'session')
                         ->leftJoin('insc.facture', 'facture')
                         ->andWhere('facture.id = :factureId')
                         ->setParameter('factureId', $facture)
                         ->orderBy('session.dateSession', 'ASC');
    }

    /**
     * @param User $user
     * @param Domaine $domain
     *
     * @return array
     */
    public function getInscriptionsForUser(User $user, Domaine $domain = null)
    {
        $queryBuilder = $this->createQueryBuilder('inscription')
            ->addSelect('session', 'refEtatInscription', 'user')
            ->join('inscription.session', 'session')
            ->join('inscription.etatInscription', 'refEtatInscription', Join::WITH, 'refEtatInscription.id != :canceledStatusId')
            ->join('inscription.user', 'user', Join::WITH, 'user.id = :userId')
            ->setParameters([
                'userId' => $user->getId(),
                'canceledStatusId' => SessionStatus::STATUT_FORMATION_CANCELED_ID,
            ])
            ->orderBy('session.dateSession', 'DESC')
        ;

        if (null !== $domain) {
            $queryBuilder
                ->join('session.module', 'module')
                ->join('module.domaines', 'domain', Join::WITH, 'domain.id = :domainId')
                ->setParameter('domainId', $domain->getId())
            ;
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Retourne la date de début de session de la première inscription de chaque utilisateur pour chaque module.
     *
     * @param $usersId
     *
     * @return QueryBuilder
     */
    public function getInscriptionsByUser($usersId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select(
                'user.id as userId,
                session.dateSession as date,
                session.id as sessionId,
                module.id as moduleId'
            )
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'insc')
            ->leftJoin('insc.user', 'user')
            ->leftJoin('insc.session', 'session')
            ->leftJoin('session.module', 'module')
            ->join('insc.etatParticipation', 'etat', Join::WITH, 'etat.id = :okStatusId')
            ->where('user.id IN (:users)')
            ->setParameter('users', $usersId)
            ->setParameter('okStatusId', SessionStatus::STATUT_PARTICIPATION_OK_ID)
            ->orderBy('insc.dateInscription')
            ->groupBy('user.id, module.id');

        return $qb;
    }

    /**
     * Retourne le nombre d'inscriptions pour l'année.
     *
     * @param int     $annee Année
     * @param Domaine[] $domains
     *
     * @return int Total
     */
    public function countInscriptionsByYear($annee, $domains)
    {
        $anneeCourantePremierJour = new \DateTime();
        $anneeCourantePremierJour->setDate($annee, 1, 1);
        $anneeCourantePremierJour->setTime(0, 0, 0);
        $anneeSuivanteDernierJour = new \DateTime();
        $anneeSuivanteDernierJour->setDate($annee + 1, 1, 1);
        $anneeSuivanteDernierJour->setTime(0, 0, 0);

        $queryBuilder = $this->createQueryBuilder('inscription');
        $queryBuilder
            ->select('COUNT(DISTINCT(inscription.id)) AS total')
            ->innerJoin('inscription.session', 'session')
            ->innerJoin('session.module', 'module')
            ->innerJoin(
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
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte('session.dateSession', ':anneeCourantePremierJour'),
                $queryBuilder->expr()->lt('session.dateSession', ':anneeSuivanteDernierJour')
            ))
            ->andWhere($queryBuilder->expr()->eq('inscription.etatInscription', ':etatInscriptionAccepte'))
            ->setParameters([
                'etatInscriptionAccepte' => SessionStatus::STATUT_FORMATION_ACCEPTED_ID,
                'anneeCourantePremierJour' => $anneeCourantePremierJour,
                'anneeSuivanteDernierJour' => $anneeSuivanteDernierJour,
            ])
        ;

        return intval($queryBuilder->getQuery()->getSingleScalarResult());
    }

    /**
     * Retourne le nombre d'utilisateurs uniques inscrits pour l'année.
     *
     * @param int     $annee Année
     * @param Domaine[] $domains
     *
     * @return int Total
     */
    public function countUsersByYear($annee, $domains)
    {
        $anneeCourantePremierJour = new \DateTime();
        $anneeCourantePremierJour->setDate($annee, 1, 1);
        $anneeCourantePremierJour->setTime(0, 0, 0);
        $anneeSuivanteDernierJour = new \DateTime();
        $anneeSuivanteDernierJour->setDate($annee + 1, 1, 1);
        $anneeSuivanteDernierJour->setTime(0, 0, 0);

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(DISTINCT(user.id)) AS total')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->innerJoin('user.inscriptions', 'inscription')
            ->innerJoin('inscription.session', 'session', Join::WITH, $queryBuilder->expr()->andX(
                $queryBuilder->expr()->gte('inscription.dateInscription', ':anneeCourantePremierJour'),
                $queryBuilder->expr()->lt('inscription.dateInscription', ':anneeSuivanteDernierJour')
            ))
            ->innerJoin('session.module', 'module')
            ->innerJoin(
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
            ->where($queryBuilder->expr()->eq('inscription.etatInscription', ':etatInscriptionAccepte'))
            ->setParameters([
                'etatInscriptionAccepte' => SessionStatus::STATUT_FORMATION_ACCEPTED_ID,
                'anneeCourantePremierJour' => $anneeCourantePremierJour,
                'anneeSuivanteDernierJour' => $anneeSuivanteDernierJour,
            ])
        ;

        return intval($queryBuilder->getQuery()->getSingleScalarResult());
    }

    /**
     * @return int
     */
    public function getAmountOfSessionWithoutBill()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('SUM(i.total)')
            ->from('HopitalNumeriqueModuleBundle:Inscription', 'i')
            ->leftJoin('i.facture', 'f')
            ->where('f.id IS NULL')
            ->andWhere('i.etatParticipation IN (:status)')
            ->setParameter('status', $this->adminGridSessionStatus)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count ambassadors trained in MAPF module, in corresponding $domains.
     *
     * @param $domains
     *
     * @return int
     */
    public function countAmbassadorsTrainedInMAPFByDomains($domains)
    {
        $qb = $this->createQueryBuilder('inscription');
        $qb
            ->select('COUNT(inscription.id)')
            // Only done participation
            ->join(
                'inscription.etatParticipation',
                'participation',
                Join::WITH,
                $qb->expr()->eq('participation.id', SessionStatus::STATUT_PARTICIPATION_OK_ID)
            )
            // Only AMBASSADEUR user role
            ->join(
                'inscription.user',
                'user',
                Join::WITH,
                $qb->expr()->like(
                    'user.roles',
                    $qb->expr()->literal(sprintf('%%%s%%', Role::$ROLE_AMBASSADEUR_LABEL))
                )
            )
            ->join('inscription.session', 'session')
            // Only MAPF module
            ->join('session.module', 'module', Join::WITH, $qb->expr()->eq('module.id', 6))
            // Only active module
            ->join('module.statut', 'status', Join::WITH, $qb->expr()->eq('status.id', Reference::STATUT_ACTIF_ID))
            // Only active sessions
            ->join('session.etat', 'etat', Join::WITH, $qb->expr()->eq('etat.id', SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID))
            ->join(
                'module.domaines',
                'domaine',
                Join::WITH,
                $qb->expr()->in(
                    'domaine',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
