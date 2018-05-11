<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Message;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Member\ViewedMember;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Doctrine\ORM\EntityRepository;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Inscription;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use Nodevo\RoleBundle\Entity\Role;
use Doctrine\Common\Collections\Collection;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * UserRepository.
 */
class UserRepository extends EntityRepository
{
    /**
     * @param string $userEmail
     *
     * @return User|null
     */
    public function findUserByEmail($userEmail)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :userEmail')->setParameter('userEmail', $userEmail)
            ->getQuery()->getOneOrNullResult()
        ;
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @param $conditions
     *
     * @return QueryBuilder
     */
    public function getDatasForGrid($conditions)
    {
        /** @var User $currentUser */
        $currentUser = $conditions->value;
        $domainesId = $currentUser->getDomaines()->map(function (Domaine $domaine) {
            return $domaine->getId();
        });

        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id,
                        user.registrationDate,
                        user.username,
                        CONCAT(CONCAT(\'<a href="/?_switch_user=\', user.username), \'" target="_blank" class="btn btn-magenta fa fa-user" title="Simuler"></a>\') AS usernameSimulated,
                        user.pseudonym,
                        user.email,
                        user.lastname,
                        user.firstname,
                        user.alreadyBeAmbassadeur,
                        user.alreadyBeExpert,
                        user.activityNewsletterEnabled,
                        refRegion.libelle as region,
                        user.roles,
                        refEtat.libelle as etat,
                        user.lock,
                        min(contractualisation.dateRenouvellement) as contra,
                        user.visitCount,
                        GROUP_CONCAT(DISTINCT user_domaines.nom SEPARATOR \' - \') as domaines
            ')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.etat', 'refEtat')
            ->leftJoin('user.region', 'refRegion')
            ->leftJoin('user.contractualisations', 'contractualisation', Join::WITH, 'contractualisation.archiver = 0')
            ->join('user.domaines', 'user_domaines', Join::WITH, 'user_domaines.id IN (:domaines_ids)')
            ->groupBy('user.id')
            ->orderBy('user.registrationDate', 'DESC')
            ->addOrderBy('user.username')
        ->setParameters([
            'domaines_ids' => $domainesId,
        ]);

        return $qb;
    }

    /**
     * Override : Récupère les données Etablissement pour le grid sous forme de tableau.
     *
     * @return QueryBuilder
     */
    public function getEtablissementForGrid($conditions)
    {
        /** @var User $currentUser */
        $currentUser = $conditions->value;
        $domainIds = $currentUser->getDomaines()->map(function (Domaine $domaine) {
            return $domaine->getId();
        });

        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id,
                     user.username,
                     user.lastname,
                     user.firstname,
                     refRegion.libelle as region,
                     user.organizationLabel,
                     user.archiver,
                     GROUP_CONCAT(domains.nom SEPARATOR \', \') as domainName

            ')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->join('user.domaines', 'domains', Join::WITH, 'domains.id IN (:domainIds)')
            ->setParameter('domainIds', $domainIds)
            ->leftJoin('user.region', 'refRegion')
            ->where('user.organizationLabel IS NOT NULL')
            ->groupBy('user.id')
            ->orderBy('user.username')
        ;

        return $qb;
    }

    /**
     * Récupère les Etablissement pour l'export.
     *
     * @param $ids
     *
     * @return QueryBuilder
     */
    public function getEtablissementForExport($ids)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id,
                     user.username,
                     user.lastname,
                     user.firstname,
                     refRegion.libelle as region,
                     user.organizationLabel,
                     user.archiver,
                     GROUP_CONCAT(domaines.nom SEPARATOR \', \') as domainName
            ')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.region', 'refRegion')
            ->leftJoin('user.domaines', 'domaines')
            ->andWhere('user.organizationLabel IS NOT NULL', 'user.id IN (:ids)')
            ->groupBy('user.id')
            ->orderBy('user.username')
            ->setParameter('ids', $ids);

        return $qb;
    }

    /**
     * On cherche a savoir si un user existe avec le role et la région de l'user modifié.
     *
     * @param User $user L'utilisateur modifié
     *
     * @return QueryBuilder
     */
    public function userExistForRoleDirection($user)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->andWhere('user.roles LIKE :role', 'user.organization IS NOT NULL')
            ->andWhere('user.organization = :organization', 'user.id != :id')
            ->setParameter('id', $user->getId())
            ->setParameter('role', '%ROLE_ES_DIRECTION_GENERALE_5%')
            ->setParameter('organization', $user->getOrganization());

        return $qb;
    }

    /**
     * Retourne la liste des ambassadeurs de la région $region.
     *
     * @param Reference $region  La région filtrée
     * @param int       $domaine Le domaine
     *
     * @return QueryBuilder
     */
    public function getAmbassadeursByRegionAndDomaine($region, $domaine)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.rattachementRegions', 'rattachementRegion')
            ->andWhere('user.roles LIKE :role')
            ->setParameter('role', '%ROLE_AMBASSADEUR_7%')
            ->andWhere('user.enabled = 1')
            ->andWhere($qb->expr()->orX('user.region = :region', 'rattachementRegion.id = :region'))
            ->setParameter('region', $region)
            ->orderBy('user.lastname', 'ASC')
        ;

        if (!is_null($domaine) && $domaine != 0) {
            $qb->innerJoin('user.connaissancesAmbassadeurs', 'domaines', join::WITH, 'domaines.domaine = :domaine')
                ->setParameter('domaine', $domaine)
                ->andWhere('domaines.connaissance IS NOT NULL');
        }

        return $qb;
    }

    /**
     * @param $criteres
     *
     * @return QueryBuilder
     */
    public function getUsersGroupeEtablissement($criteres)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->andWhere('user.enabled = 1')
            ->andWhere('user.roles LIKE :role')
            ->setParameter('role', '%ROLE_ES_8%');

        foreach ($criteres as $critereChamp => $critereValeur) {
            if (is_array($critereValeur)) {
                $qb->andWhere('user.' . $critereChamp . ' IN (' . implode(',', $critereValeur) . ')');
            } else {
                $qb->andWhere('user.' . $critereChamp . ' = :' . $critereChamp)
                    ->setParameter($critereChamp, $critereValeur);
            }
        }

        return $qb;
    }

    /**
     * Retourne la liste des ambassadeurs de la région et de la publication.
     *
     * @param Reference $region La région filtrée
     * @param Objet     $objet  La publication
     *
     * @return QueryBuilder
     */
    public function getAmbassadeursByRegionAndProduction($region, $objet)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.objets', 'objets')
            ->andWhere('user.roles LIKE :role', 'user.enabled = 1')
            ->andWhere('objets.id = :objet')
            ->setParameter('objet', $objet)
            ->setParameter('role', '%ROLE_AMBASSADEUR_7%');

        if ($region) {
            $qb->andWhere('user.region = :region')
                ->setParameter('region', $region);
        }

        return $qb;
    }

    /**
     * Retourne la liste des utilisateurs possédant le role demandé.
     *
     * @param string $role Le rôle demandé
     *
     * @return QueryBuilder
     */
    public function findUsersByRole($role)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->where('user.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
            ->orderBy('user.lastname', 'ASC')
            ->addOrderBy('user.firstname', 'DESC');

        return $qb;
    }

    /**
     * Retourne la liste des utilisateurs possédant les roles demandés.
     *
     * @param $roles
     *
     * @return QueryBuilder
     */
    public function findUsersByRoles($roles)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user');

        foreach ($roles as $key => $role) {
            $qb->orWhere('user.roles LIKE :role' . $key)
                    ->setParameter('role' . $key, '%' . $role . '%');
        }

        $qb->orderBy('user.lastname', 'ASC')
                ->addOrderBy('user.firstname', 'DESC');

        return $qb;
    }

    /**
     * Retourne la liste des utilisateurs étant assigné au domaine.
     *
     * @param int $idDomaine Identifiant du domaine à filtrer
     *
     * @return QueryBuilder
     */
    public function findUsersByDomaine($idDomaine)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.domaines', 'domaine')
            ->where('domaine.id = :idDomaine')
            ->setParameter('idDomaine', $idDomaine);

        return $qb;
    }

    /**
     * Retourne les utilisateurs liés à un de ces domaines.
     *
     * @param \Doctrine\Common\Collections\Collection $domaines Domaines
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Utilisateurs
     */
    public function findByDomaines(Collection $domaines)
    {
        return $this->createQueryBuilder('user')
            ->join('user.domaines', 'domaine', Expr\Join::WITH)
            ->andWhere('domaine.id IN (:domaines)')
            ->orderBy('user.lastname')
            ->setParameter('domaines', $domaines->toArray())
            ->getQuery()
            ->getResult();
    }

    /**
     * Return users linked to network and defining one of specified domains (i.e. users referenced with at least one 'networkJob').
     *
     * @param \Doctrine\Common\Collections\Collection $domaines Domains
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Utilisateurs
     */
    public function findNetworkUsersByDomaines(Collection $domains)
    {
        return $this->createQueryBuilder('user')
            ->join('user.domaines', 'domaine', Expr\Join::WITH)
            ->join(EntityHasReference::class, 'hasReference', Expr\Join::WITH,
                'hasReference.entityId = user.id AND hasReference.entityType = :entityType')
            ->join('hasReference.reference', 'reference')
            ->join('reference.codes', 'referenceCode', Expr\Join::WITH,
                'referenceCode.label = :referenceCode')
            ->andWhere('domaine.id IN (:domaines)')
            ->orderBy('user.lastname')
            ->setParameter('entityType', Entity::ENTITY_TYPE_AMBASSADEUR)
            ->setParameter('referenceCode', 'ROLE_RESEAU')
            ->setParameter('domaines', $domains->toArray())
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le premier utilisateur correspondant au role et à la région demandés.
     *
     * @param string $role     Le rôle demandé
     * @param int    $idRegion Region demandée
     *
     * @return QueryBuilder
     */
    public function findUsersByRoleAndRegion($idRegion, $role)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->where('user.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
            ->andWhere('user.region = :idRegion')
            ->setParameter('idRegion', $idRegion)
            ->andWhere('user.enabled = 1')
            ->setMaxResults(1)
        ;

        return $qb;
    }

    /**
     * Retourne un unique CMSI.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User|null Un CMSI si trouvé, sinon NIL
     */
    public function getCmsi(array $criteres)
    {
        return $this->findOneByRole(Role::$ROLE_CMSI_LABEL, $criteres);
    }

    /**
     * Retourne un unique directeur.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User|null Un directeur si trouvé, sinon NIL
     */
    public function getDirecteur(array $criteres)
    {
        return $this->findOneByRole(Role::$ROLE_DIRECTEUR_LABEL, $criteres);
    }

    /**
     * Retourne un unique utilisateur en fonction d'un rôle.
     *
     * @param string $role     Label du rôle sur lequel filtrer
     * @param array  $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User Un utilisateur si trouvé, sinon NIL
     */
    private function findOneByRole($role, array $criteres)
    {
        $utilisateurs = $this->findByRole($role, $criteres);
        if (count($utilisateurs) > 0) {
            return $utilisateurs[0];
        }

        return null;
    }

    /**
     * Retourne une liste d'ambassadeurs.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return User[] La liste des ambassadeurs
     */
    public function getAmbassadeurs(array $criteres = [])
    {
        return $this->findByRole(Role::$ROLE_AMBASSADEUR_LABEL, $criteres);
    }

    /**
     * Retourne une liste des experts.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return User[] La liste des experts
     */
    public function getExperts(array $criteres = [])
    {
        return $this->findByRole(Role::$ROLE_EXPERT_LABEL, $criteres);
    }

    /**
     * Retourne une liste d'utilisateurs ES ou Enregistré.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return User[] La liste des utilisateurs
     */
    public function getESAndEnregistres(array $criteres = [])
    {
        return $this->findByRole([Role::$ROLE_ES_LABEL, Role::$ROLE_ENREGISTRE_LABEL], $criteres);
    }

    /**
     * Retourne une liste d'utilisateurs Admins.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return User[] La liste des utilisateurs
     */
    public function getAdmins(array $criteres = [])
    {
        return $this->findByRole([Role::$ROLE_ADMIN_HN_LABEL, Role::$ROLE_ADMIN_LABEL], $criteres);
    }

    /**
     * @return User[]
     */
    public function getAdminsAndDomainAdmins()
    {
        return $this->findByRole([Role::$ROLE_ADMIN_LABEL, Role::$ROLE_ADMIN_DOMAINE, Role::$ROLE_ADMIN_HN_LABEL], []);
    }

    /**
     * Retourne une liste d'utilisateurs Cmsi.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return User[] La liste des utilisateurs
     */
    public function getcmsis(array $criteres = [])
    {
        return $this->findByRole([Role::$ROLE_CMSI_LABEL], $criteres);
    }

    /**
     * Retourne une liste d'utilisateurs en fonction d'un rôle.
     *
     * @param string|array $role     Label(s) du(es) rôle(s) sur lequel(lesquels) filtrer
     * @param array        $criteres Filtres à appliquer sur la liste
     *
     * @return User[] La liste des utilisateurs
     */
    private function findByRole($role, array $criteres)
    {
        $requete = $this->_em->createQueryBuilder();

        $requete
            ->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
        ;

        if (!is_array($role)) {
            $requete
                ->where('user.roles LIKE :role')->setParameter('role', '%' . $role . '%')
            ;
        } else {
            for ($i = 0, $count = count($role); $i < $count; ++$i) {
                $requete
                    ->orWhere('user.roles LIKE :role' . $i)->setParameter('role' . $i, '%' . $role[$i] . '%')
                ;
            }
        }

        foreach ($criteres as $critereChamp => $critereValeur) {
            if (is_array($critereValeur)) {
                $requete
                    ->andWhere(
                        $requete->expr()->in('user.' . $critereChamp, $critereValeur)
                    )
                ;
            } else {
                $requete
                    ->andWhere('user.' . $critereChamp . ' = :' . $critereChamp)
                    ->setParameter($critereChamp, $critereValeur)
                ;
            }
        }

        $requete
            ->addOrderBy('user.lastname', 'ASC')
            ->addOrderBy('user.firstname', 'ASC')
        ;

        return $requete->getQuery()->getResult();
    }

    /**
     * Retourne une liste d'utilisateurs en fonction d'un rôle en respectant le retour d'un QB
     * et non d'une liste d'utilisateur ainsi que le public pour l'utilisateur dans des formType.
     *
     * @author gmelchilsen <gmelchilsen@nodevo.com>
     *
     * @param string|array $role     Label(s) du(es) rôle(s) sur lequel(lesquels) filtrer
     * @param array        $criteres Filtres à appliquer sur la liste
     *
     * @return QueryBuilder
     */
    public function getUsersByRole($role, array $criteres = [])
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
        ;

        if (!is_array($role)) {
            $qb
                ->where('user.roles LIKE :role')->setParameter('role', '%' . $role . '%')
            ;
        } else {
            $nbRole = count($role);

            for ($i = 0; $i < $nbRole; ++$i) {
                $qb
                    ->orWhere('user.roles LIKE :role' . $i)->setParameter('role' . $i, '%' . $role[$i] . '%')
                ;
            }
        }

        foreach ($criteres as $critereChamp => $critereValeur) {
            if (is_array($critereValeur)) {
                $qb
                    ->andWhere(
                        $qb->expr()->in('user.' . $critereChamp, $critereValeur)
                    )
                ;
            } else {
                $qb
                    ->andWhere('user.' . $critereChamp . ' = :' . $critereChamp)
                    ->setParameter($critereChamp, $critereValeur)
                ;
            }
        }

        $qb
            ->addOrderBy('user.lastname', 'ASC')
            ->addOrderBy('user.firstname', 'ASC')
        ;

        return $qb;
    }

    /**
     * Récupère les utilisateurs ayant répondues au questionnaire passé en paramètre.
     *
     * @param int $idQuestionnaire
     *
     * @return QueryBuilder
     */
    public function getUsersByQuestionnaire($idQuestionnaire)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->innerJoin('user.reponses', 'reponses')
            ->innerJoin('reponses.question', 'question')
            ->innerJoin('question.questionnaire', 'questionnaire', 'WITH', 'questionnaire.id = :idQuestionnaire')
            ->setParameter('idQuestionnaire', $idQuestionnaire)
            ->groupBy('user')
            ->orderBy('user.lastname', 'ASC')
            ->addOrderBy('user.firstname');

        return $qb;
    }

    /**
     * Récupère tous les utilisateurs (tous les rôles).
     *
     * @return QueryBuilder
     */
    public function getAllUsers()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user');

        return $qb;
    }

    /**
     * @return User[]
     */
    public function getUsersId()
    {
        return $this->createQueryBuilder('user')
            ->select('user.id')

            ->getQuery()->getScalarResult()
        ;
    }

    /**
     * @return array
     */
    public function getVisitsCountGroupedByUser()
    {
        return $this->createQueryBuilder('user', 'user.id')
            ->select('user.id, user.visitCount')

            ->getQuery()->getResult()
        ;
    }

    /**
     * Récupère le nombre d'établissements connectés.
     *
     * @param Domaine $domaine
     *
     * @return int
     */
    public function getNbEtablissements(Domaine $domaine = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(distinct(user.organization))')
           ->from('HopitalNumeriqueUserBundle:User', 'user')
           ->where('user.organization IS NOT NULL')
        ;

        if (null !== $domaine) {
            $qb
                ->join('user.domaines', 'domaines', Join::WITH, 'domaines.id = :domaine')
                ->setParameter('domaine', $domaine)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getCDPNewMembersCount(User $user, Groupe $groupe = null, Domaine $domaine = null)
    {
        return count($this->getCommunautePratiqueMembresQueryBuilder($groupe, $domaine)
            ->andWhere('user.communautePratiqueEnrollmentDate > :userEnrollmentDate')
            ->setParameter('userEnrollmentDate', $user->getCommunautePratiqueEnrollmentDate())
            ->leftJoin(ViewedMember::class, 'view', Join::WITH, 'view.viewer = :user AND view.member = user')
            ->setParameter('user', $user)
            ->andWhere('view.viewedAt IS NULL')

            ->getQuery()->getResult())
        ;
    }

    /**
     * Retourne la QueryBuilder avec les membres de la communauté de pratique.
     *
     * @param Groupe|null  $groupe
     * @param Domaine|null $domaine
     * @param null         $membreId
     * @param bool $onlyRegisteredUser
     *
     * @return QueryBuilder
     */
    public function getCommunautePratiqueMembresQueryBuilder(
        Groupe $groupe = null,
        Domaine $domaine = null,
        $membreId = null,
        $onlyRegisteredUser = true
    ) {
        $query = $this->createQueryBuilder('user');

        $query
            ->select('user, profileType, region, esStatut, activities')
            ->leftJoin('user.profileType', 'profileType')
            ->leftJoin('user.region', 'region')
            ->leftJoin('user.organizationType', 'esStatut')
            ->leftJoin('user.activities', 'activities')
            ->andWhere('user.etat = :etat')
            ->setParameter('etat', User::ETAT_ACTIF_ID)
            ->addOrderBy('user.lastname', 'ASC')
            ->addOrderBy('user.firstname', 'ASC')
            ->addOrderBy('user.id', 'ASC')
        ;

        if ($onlyRegisteredUser) {
            $query
                ->andWhere('user.inscritCommunautePratique = :inscritCommunautePratique')
                ->setParameter('inscritCommunautePratique', true)
            ;
        }

        if (null !== $groupe) {
            $query
                ->innerJoin(
                    'user.groupeInscription',
                    'groupeInscription',
                    Join::WITH,
                    'groupeInscription.groupe = :groupe'
                )
                ->setParameter('groupe', $groupe)
            ;
        }

        if (null !== $domaine) {
            $query
                ->innerJoin('user.domaines', 'domaine', Join::WITH, 'domaine = :domaine')
                ->setParameter('domaine', $domaine)
            ;
        }

        if (null !== $membreId) {
            $query
            ->andWhere('user.id = :membreId')
            ->setParameter('membreId', $membreId)
            ;
        }

        return $query;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return User[]
     */
    public function getCommunautePratiqueMembersInDomains($domains)
    {
        return $this->createQueryBuilder('user')
            ->join('user.domaines', 'domains', Join::WITH, 'domains.id IN (:domains)')
            ->setParameter('domains', $domains)
            ->andWhere('user.inscritCommunautePratique = TRUE')

            ->getQuery()->getResult()
        ;
    }

    /**
     * Retourne la QueryBuilder avec les membres d'un groupe de la communauté de pratique.
     *
     * @param Groupe $groupe Groupe des membres
     *
     * @return QueryBuilder QueryBuilder
     */
    public function getCommunautePratiqueUsersByGroupeQueryBuilder(Groupe $groupe)
    {
        $query = $this->createQueryBuilder('groupeUser');

        $query
            ->innerJoin(
                'groupeUser.groupeInscription',
                'groupeUserGroupe',
                Join::WITH,
                'groupeUserGroupe.groupe = :groupeUserGroupe'
            )
            ->setParameter('groupeUserGroupe', $groupe)
        ;

        return $query;
    }

    /**
     * Retourne les membres de la communauté de pratique n'appartenant pas à tel groupe.
     *
     * @param Domaine $domain
     * @param Groupe $groupe Groupe
     *
     * @return User[] Utilisateurs
     */
    public function findCommunautePratiqueMembresNotInGroupe(Domaine $domaine, Groupe $groupe = null)
    {
        $query = $this->createQueryBuilder('user');

        $groupeUsers = $this->getCommunautePratiqueUsersByGroupeQueryBuilder($groupe)->getQuery()->getResult();

        $query
            ->leftJoin('user.groupeInscription', 'groupeInscription')
            ->andWhere('user.inscritCommunautePratique = :inscritCommunautePratique')
            ->setParameter('inscritCommunautePratique', true)
            ->andWhere('user.etat = :etat')
            ->setParameter('etat', User::ETAT_ACTIF_ID)
            ->leftJoin('user.domaines', 'domaine')
            ->andWhere('domaine.url = :domaine')
            ->setParameter(':domaine', ($domaine) ? $domaine->getUrl() : null)
            ->addOrderBy('user.lastname', 'ASC')
            ->addOrderBy('user.firstname', 'ASC')
            ->addOrderBy('user.id', 'ASC')
        ;

        if (count($groupeUsers) > 0) {
            $query
                ->andWhere($query->expr()->notIn('user', ':groupeUsers'))
                ->setParameter('groupeUsers', $groupeUsers)
            ;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne des membres de la communauté de pratique au hasard.
     *
     * @param Domaine    $domaine
     * @param int        $nombreMembres
     * @param array|null $ignores
     *
     * @return User[]
     */
    public function findCommunautePratiqueRandomMembres(Domaine $domaine, $nombreMembres, array $ignores = null)
    {
        $query = $this->createQueryBuilder('user');

        $query
            ->select('user', 'RAND() AS HIDDEN rand')
            ->join('user.domaines', 'domaine', Join::WITH, 'domaine.id = :domaine_id')
                ->setParameter('domaine_id', $domaine->geTId())
            ->andWhere('user.inscritCommunautePratique = :inscritCommunautePratique')
            ->setParameter('inscritCommunautePratique', true)
            ->andWhere('user.etat = :etat')
            ->setParameter('etat', User::ETAT_ACTIF_ID)
            ->setMaxResults($nombreMembres)
            ->orderBy('rand')
        ;

        if (null !== $ignores) {
            $query
                ->andWhere($query->expr()->notIn('user', ':ignores'))
                ->setParameter('ignores', $ignores)
            ;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne de nombre de membres de la communauté de pratique.
     *
     * @param Domaine $domaine
     *
     * @return int Total
     */
    public function findCommunautePratiqueMembresCount(Domaine $domaine)
    {
        $query = $this->createQueryBuilder('user');

        $query
            ->select('COUNT(user)')
            ->join('user.domaines', 'domaine', Join::WITH, 'domaine.id = :domaine_id')
            ->setParameter('domaine_id', $domaine->geTId())
            ->where('user.inscritCommunautePratique = :inscritCommunautePratique')
            ->setParameter('inscritCommunautePratique', true)
            ->andWhere('user.etat = :etat')
            ->setParameter('etat', User::ETAT_ACTIF_ID)
        ;

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Domaine[] $domains
     *
     * @return integer|null
     */
    public function getCDPOrganizationsCount(array $domains)
    {
        if (empty($domains)) {
            return null;
        }

        return $this->createQueryBuilder('user')
            ->select('COUNT(DISTINCT organization.id)')
            ->join('user.organization','organization')
            ->join('user.domaines', 'domaines', Join::WITH, 'domaines IN (:domains)')
            ->setParameter('domains', $domains)
            ->andWhere('user.inscritCommunautePratique = TRUE')

            ->getQuery()->getSingleScalarResult()
        ;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return integer
     */
    public function countCDPUsers($domains)
    {
        if (empty($domains)) {
            return null;
        }

        $qb = $this->createQueryBuilder('user');

        return $qb
            ->select('COUNT(DISTINCT user.id)')
            ->join(
                'user.domaines',
                'domaine',
                Join::WITH,
                $qb->expr()->in(
                    'domaine',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )
            ->andWhere('user.inscritCommunautePratique = TRUE')

            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param Domaine[] $domains
     * @param User|null $user
     *
     * @return null|integer
     */
    public function getCDPContributorCount(array $domains, User $user = null)
    {
        if (empty($domains)) {
            return null;
        }

        $qb = $this->createQueryBuilder('user');

        $qb
            ->select('COUNT(DISTINCT user.id)')
            ->join(Message::class,'message', Join::WITH, 'message.user = user')
            ->join('message.discussion', 'discussion')
            ->join('discussion.domains', 'discussion_domain', Join::WITH, 'discussion_domain IN (:domains)')
            ->join(
                'user.domaines',
                'domain',
                Join::WITH,
                'domain.id IN (:domains)'
            )
            ->leftJoin('discussion.groups', 'groups')
            ->setParameter('domains', $domains)
            ->andWhere('user.inscritCommunautePratique = TRUE')
        ;

        if ($user) {
            if (!$user->hasRoleCDPAdmin()) {
                $qb
                    ->leftJoin('groups.requiredRoles', 'requiredRole')
                    ->andWhere('requiredRole.role IN (:userRoles) OR groups.requiredRoles is empty')
                    ->setParameter('userRoles', $user->getRoles())
                ;
            }
        } else {
            $qb
                ->andWhere('discussion.groups is empty OR groups.requiredRoles is empty')
            ;
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return integer
     */
    public function countAddCDPUsers()
    {
        return $this->createQueryBuilder('user')
            ->select('COUNT(DISTINCT user.id)')
            ->andWhere('user.inscritCommunautePratique = TRUE')

            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param Domaine[] $domains
     *
     * @return integer
     */
    public function countUsersInCDP($domains)
    {
        if (empty($domains)) {
            return null;
        }

        $qb = $this->_em->createQueryBuilder();

        return $qb
            ->select('COUNT(DISTINCT user.id)')
            ->from(Inscription::class, 'inscription')
            ->join('inscription.user', 'user')

            ->join(
                'user.domaines',
                'domaine',
                Join::WITH,
                $qb->expr()->in(
                    'domaine',
                    array_map(function (Domaine $domain) {
                        return $domain->getId();
                    }, $domains)
                )
            )

            ->getQuery()->getSingleScalarResult()
        ;
    }

    /**
     * Count users in givent domains
     *
     * @param Domaine[] $domains
     *
     * @return int
     */
    public function countUsersByDomains($domains)
    {
        $qb = $this->createCountByDomainsQueryBuilder($domains);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count users by domains who logged in since $since
     *
     * @param Domaine[] $domains
     * @param $since
     *
     * @return int
     */
    public function countActiveUsersByDomains($domains, $since)
    {
        $qb = $this->createCountByDomainsQueryBuilder($domains);
        $qb
            ->join('user.etat', 'etat', Join::WITH, $qb->expr()->eq('etat.id', Reference::STATUT_ACTIF_ID))
            ->andWhere('user.lastLogin IS NOT NULL')
            ->andWhere('user.lastLogin > :since')
            ->setParameter('since', $since)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count ES users by domains
     *
     * @param Domaine[] $domains
     *
     * @return int
     */
    public function countEsUsersByDomains($domains)
    {
        $qb = $this->createCountByDomainsQueryBuilder($domains);
        $qb
            ->join('user.etat', 'etat', Join::WITH, $qb->expr()->eq('etat.id', Reference::STATUT_ACTIF_ID))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('user.roles', $qb->expr()->literal(sprintf('%%%s%%', Role::$ROLE_DIRECTEUR_LABEL))),
                    $qb->expr()->like('user.roles', $qb->expr()->literal(sprintf('%%%s%%', Role::$ROLE_ES_LABEL)))
                )
            )
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Create base query builder for user count by domains queries
     *
     * @param Domaine[] $domains
     *
     * @return QueryBuilder
     */
    private function createCountByDomainsQueryBuilder($domains)
    {
        $qb = $this->createQueryBuilder('user');
        $qb
            ->select('COUNT(DISTINCT user.id)')
            ->join(
                'user.domaines',
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

        return $qb;
    }

    /**
     * @return integer
     */
    public function countAllUsers()
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()->getSingleScalarResult()
        ;
    }

    /**
     * @param Domaine|null $domain
     * @param int $limit
     *
     * @return User[]
     */
    public function getLastUserEnrolledInCDP(Domaine $domain = null, $limit = 20)
    {
        $queryBuilder = $this->createQueryBuilder('user')
            ->andWhere('user.inscritCommunautePratique = TRUE')
        ;

        if ($domain) {
            $queryBuilder
                ->join('user.domaines', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain->getId())
            ;
        }

        return $queryBuilder
            ->addOrderBy('user.communautePratiqueEnrollmentDate', 'DESC')
            ->setMaxResults($limit)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Domaine|null $domain
     * @param int $limit
     *
     * @return User[]
     */
    public function getLastUpdatedUser(Domaine $domain = null, $limit = 20)
    {
        $queryBuilder = $this->createQueryBuilder('user')
            ->andWhere('user.inscritCommunautePratique = TRUE')
        ;

        if ($domain) {
            $queryBuilder
                ->join('user.domaines', 'domain', Join::WITH, 'domain.id = :domain')
                ->setParameter('domain', $domain->getId())
            ;
        }

        return $queryBuilder
            ->addOrderBy('user.dateLastUpdate', 'DESC')
            ->setMaxResults($limit)

            ->getQuery()->getResult()
        ;
    }

    /**
     * @param Groupe $group
     *
     * @return array
     */
    public function getCommunautePratiqueUsersInGroup(Groupe $group)
    {
        return $this->createQueryBuilder('user')
            ->join('user.groupeInscription', 'groupeInscription', Join::WITH, 'groupeInscription.groupe = :groupe')
            ->setParameter('groupe', $group)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return QueryBuilder
     */
    public function createCommunautePratiqueUsersQueryBuilder()
    {
        return $this->createQueryBuilder('user')
            ->select('user.id')
            ->where('user.inscritCommunautePratique = TRUE')
        ;
    }
}
