<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query\AST\Join;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\ORM\Query\Expr;

/**
 * Repository de Groupe.
 */
class GroupeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Retourne les groupes n'ayant pas encore démarrés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @param bool                                           $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     * @param bool $checkRegistration
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non démarrés
     */
    public function findNonDemarres(Domaine $domaine = null, User $user = null, $isActif = null, $checkRegistration = false)
    {
        $query = $this->createQueryBuilder('groupe');
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query
            ->andWhere('groupe.dateDemarrage > :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
        ;

        if (null !== $domaine) {
            $query
                ->join('groupe.domains', 'domain', Expr\Join::WITH, 'domain = :domaine')
                ->setParameter('domaine', $domaine)
            ;
        }

        if (null !== $user) {
            if ($checkRegistration) {
                $query
                    ->innerJoin('groupe.inscriptions', 'inscription', Expr\Join::WITH, 'inscription.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            if (!$user->hasRoleCDPAdmin()) {
                $query
                    ->leftJoin('groupe.requiredRoles', 'requiredRole')
                    ->andWhere('requiredRole.role IN (:userRoles) OR groupe.requiredRoles is empty')
                    ->setParameter('userRoles', $user->getRoles())
                ;
            }
        } else {
            $query->andWhere('groupe.requiredRoles is empty');
        }

        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        $query
            ->orderBy('groupe.dateDemarrage')
            ->orderBy('groupe.dateFin')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne les groupes en cours.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @param bool                                           $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findEnCours(Domaine $domaine = null, User $user = null, $isActif = null, $checkRegistration = false)
    {
        $query = $this->createQueryBuilder('groupe');
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query
            ->andWhere('groupe.dateDemarrage <= :aujourdhui')
            ->andWhere('groupe.dateFin >= :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
        ;

        if (null !== $domaine) {
            $query
                ->join('groupe.domains', 'domain', Expr\Join::WITH, 'domain = :domaine')
                ->setParameter('domaine', $domaine)
            ;
        }

        if (null !== $user) {
            $query
                ->addSelect('CASE WHEN (inscription.user IS NOT NULL) THEN 1 ELSE 0 as HIDDEN userInGroup')
                ->leftJoin('groupe.inscriptions', 'inscription', Expr\Join::WITH, 'inscription.user = :user')
                ->setParameter('user', $user)
            ;

            if ($checkRegistration) {
                $query
                    ->andWhere('inscription.user IS NOT NULL')
                ;
            }

            if (!$user->hasRoleCDPAdmin()) {
                $query
                    ->leftJoin('groupe.requiredRoles', 'requiredRole')
                    ->andWhere('requiredRole.role IN (:userRoles) OR groupe.requiredRoles is empty')
                    ->setParameter('userRoles', $user->getRoles())
                ;
            }
        } else {
            $query
                ->addSelect('0 as HIDDEN userInGroup')
                ->andWhere('groupe.requiredRoles is empty')
            ;
        }

        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        $query
            ->leftJoin('groupe.discussions', 'discussion')
            ->leftJoin('discussion.messages', 'message', Expr\Join::WITH, 'message.published = TRUE')
            ->addOrderBy('userInGroup', 'DESC')
            ->addOrderBy('message.createdAt', 'DESC')
            ->addOrderBy('groupe.dateDemarrage')
            ->addOrderBy('groupe.dateFin')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * @param Domaine[] $domains
     * @param User|null $user
     *
     * @return integer
     */
    public function countActiveGroups(array $domains = [], User $user = null)
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select('COUNT(g)')
            ->andWhere('g.actif = TRUE')
            ->andWhere('g.dateDemarrage <= :today')
            ->andWhere('g.dateFin >= :today')
            ->setParameter('today', (new \DateTime())->setTime(0, 0, 0))
        ;

        if (count($domains)) {
            $queryBuilder
                ->join('g.domains', 'domain', Expr\Join::WITH, 'domain.id IN (:domains)')
                ->setParameter('domains', $domains)
            ;
        }

        if ($user) {
            if (!$user->hasRoleCDPAdmin()) {
                $queryBuilder
                    ->leftJoin('g.requiredRoles', 'requiredRole')
                    ->andWhere('requiredRole.role IN (:userRoles) OR g.requiredRoles is empty')
                    ->setParameter('userRoles', $user->getRoles())
                ;
            }
        } else {
            $queryBuilder->andWhere('g.requiredRoles is empty');
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param User $user
     * @param integer|null $count
     * @param Domaine[] $domains
     *
     * @return Groupe[]
     */
    public function getUsersRecentGroups(User $user, $count = null, array $domains = [])
    {
        $queryBuilder = $this->createQueryBuilder('cdpGroup')
            ->join('cdpGroup.inscriptions','inscription')
            ->join('inscription.user', 'user', Expr\Join::WITH, 'user.id = :user')
            ->setParameter('user', $user)
            ->andWhere('cdpGroup.actif = TRUE')
            ->andWhere('cdpGroup.dateDemarrage <= :today')
            ->andWhere('cdpGroup.dateFin >= :today')
            ->setParameter('today', (new \DateTime())->setTime(0, 0, 0))
        ;

        if (count($domains)) {
            $queryBuilder
                ->join('cdpGroup.domains', 'domain', Expr\Join::WITH, 'domain.id IN (:domains)')
                ->setParameter('domains', $domains)
            ;
        }

        if (null !== $count) {
            $queryBuilder->setMaxResults($count);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Retourne les groupes terminés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user    Utilisateur
     * @param bool                                           $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes en cours
     */
    public function findTermines(Domaine $domaine, User $user = null, $isActif = null)
    {
        $query = $this->createQueryBuilder('groupe');
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query
            ->andWhere('groupe.dateFin < :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
        ;

        if (null !== $domaine) {
            $query
                ->join('groupe.domains', 'domain', Expr\Join::WITH, 'domain = :domaine')
                ->setParameter('domaine', $domaine)
            ;
        }

        if (null !== $user) {
            $query
                ->innerJoin('groupe.inscriptions', 'inscription', Expr\Join::WITH, 'inscription.user = :user')
                ->setParameter('user', $user)
            ;
        }
        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        $query
            ->orderBy('groupe.dateDemarrage')
            ->orderBy('groupe.dateFin')
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne les groupes non fermés.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine               Domaine
     * @param \HopitalNumerique\UserBundle\Entity\User       $user                  Utilisateur
     * @param bool                                           $enVedette             (optionnel) En vedette
     * @param bool                                           $isActif               (optionnel) Si les groupes doivent être actifs ou non actifs
     * @param bool                                           $dateInscriptionPassee (optionnel) Si la date d'inscription doit être passée ou pas
     *
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe> Groupes non fermés
     */
    public function findNonFermes(Domaine $domaine = null, User $user = null, $enVedette = null, $isActif = null, $dateInscriptionPassee = null, $checkRegistration = false)
    {
        $query = $this->createQueryBuilder('groupe');
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $query
            ->leftJoin('groupe.fiches', 'fiche')
            ->addSelect('fiche')
            ->leftJoin('groupe.inscriptions', 'groupeInscription')
            ->addSelect('groupeInscription')
            ->leftJoin('groupeInscription.user', 'groupeUser')
            ->addSelect('groupeUser')
            ->andWhere('groupe.dateFin > :aujourdhui')
            ->setParameter('aujourdhui', $aujourdhui)
            ->addOrderBy('groupe.dateDemarrage')
            ->addOrderBy('groupe.dateFin')
            ->addOrderBy('groupeUser.lastname')
            ->addOrderBy('groupeUser.firstname')
        ;

        if (null !== $domaine) {
            $query
                ->join('groupe.domains', 'domain', Expr\Join::WITH, 'domain = :domaine')
                ->setParameter('domaine', $domaine)
            ;
        }

        if (null !== $user) {
            if ($checkRegistration) {
                $query
                    ->innerJoin('groupe.inscriptions', 'inscription', Expr\Join::WITH, 'inscription.user = :user')
                    ->setParameter('user', $user)
                    ->innerJoin('inscription.user', 'user')
                    ->addOrderBy('user.lastname')
                    ->addOrderBy('user.firstname')
                ;
            }

            if (!$user->hasRoleCDPAdmin()) {
                $query
                    ->leftJoin('groupe.requiredRoles', 'requiredRole')
                    ->andWhere('requiredRole.role IN (:userRoles) OR groupe.requiredRoles is empty')
                    ->setParameter('userRoles', $user->getRoles())
                ;
            }
        } else {
            $query->andWhere('groupe.requiredRoles is empty');
        }


        if (null !== $enVedette) {
            $query
                ->andWhere('groupe.vedette = :vedette')
                ->setParameter('vedette', $enVedette)
            ;
        }
        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }
        if (null !== $dateInscriptionPassee) {
            $query->andWhere('groupe.dateInscriptionOuverture ' . ($dateInscriptionPassee ? '<=' : '>') . ' :aujourdhui');
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne la QueryBuilder des groupes ayant des publications.
     *
     * @parap Domaine|null $domain
     * @param bool $isActif (optionnel) Si les groupes doivent être actifs ou non actifs
     *
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function findWithPublicationsQueryBuilder(Domaine $domain = null, $isActif = null)
    {
        $query = $this->createQueryBuilder('groupe');

        $query
            ->innerJoin('groupe.publications', 'publication')
            ->addSelect('publication')
            ->orderBy('groupe.dateDemarrage')
        ;

        if ($domain) {
            $query
                ->join('groupe.domains', 'domain', Expr\Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        if (null !== $isActif) {
            $query
                ->andWhere('groupe.actif = :actif')
                ->setParameter('actif', $isActif)
            ;
        }

        return $query;
    }

    /**
     * Retourne les données pour le grid.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines
     *
     * @return array Données
     */
    public function getGridData(PersistentCollection $domaines)
    {
        $query = $this->createQueryBuilder('groupe');

        $query
            ->select(
                'groupe.id AS id',
                'groupe.titre AS titre',
                'groupe.dateInscriptionOuverture AS dateInscriptionOuverture',
                'groupe.dateDemarrage AS dateDemarrage',
                'groupe.dateFin AS dateFin',
                'groupe.vedette AS vedette',
                'groupe.actif AS actif',

                'GROUP_CONCAT(domaine.nom SEPARATOR \', \') AS domains'
            )
            ->innerJoin('groupe.domains', 'domaine', Expr\Join::WITH, $query->expr()->in('domaine', ':domaines'))
            ->setParameter('domaines', $domaines->toArray())
            ->groupBy('id')
        ;

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param Domaine|null $domain
     * @param int $limit
     * @param User $user
     *
     * @return Groupe[]
     */
    public function getLastClosed(Domaine $domain = null, $limit = 20, User $user = null)
    {
        $queryBuilder = $this->createQueryBuilder('cdp_group')
            ->andWhere('cdp_group.dateFin < :today')
            ->setParameter('today', new \DateTime())
        ;

        if ($domain) {
            $queryBuilder
                ->join('cdp_group.domains', 'domain', Expr\Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        if ($user) {
            if (!$user->hasRoleCDPAdmin()) {
                $queryBuilder
                    ->leftJoin('cdp_group.requiredRoles', 'requiredRole')
                    ->andWhere('requiredRole.role IN (:userRoles) OR cdp_group.requiredRoles is empty')
                    ->setParameter('userRoles', $user->getRoles())
                ;
            }
        } else {
            $queryBuilder->andWhere('cdp_group.requiredRoles is empty');
        }

        return $queryBuilder
            ->addOrderBy('cdp_group.dateFin', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine|null $domain
     * @param int $limit
     * @param User|null $user
     *
     * @return Groupe[]
     */
    public function getLastOpened(Domaine $domain = null, $limit = 20, User $user = null)
    {
        $queryBuilder = $this->createQueryBuilder('cdp_group')
            ->andWhere('cdp_group.dateFin > :today')
            ->andWhere('cdp_group.dateInscriptionOuverture < :today')
            ->setParameter('today', new \DateTime())
        ;

        if ($domain) {
            $queryBuilder
                ->join('cdp_group.domains', 'domain', Expr\Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain)
            ;
        }

        if ($user) {
            if (!$user->hasRoleCDPAdmin()) {
                $queryBuilder
                    ->leftJoin('cdp_group.requiredRoles', 'requiredRole')
                    ->andWhere('requiredRole.role IN (:userRoles) OR cdp_group.requiredRoles is empty')
                    ->setParameter('userRoles', $user->getRoles())
                ;
            }
        } else {
            $queryBuilder->andWhere('cdp_group.requiredRoles is empty');
        }

        return $queryBuilder
            ->addOrderBy('cdp_group.dateInscriptionOuverture', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult()
        ;
    }
}
