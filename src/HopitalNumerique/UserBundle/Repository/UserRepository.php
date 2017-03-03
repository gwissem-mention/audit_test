<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Nodevo\RoleBundle\Entity\Role;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Doctrine\ORM\Query\Expr;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr\Join;

/**
 * UserRepository.
 */
class UserRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté.
     *
     * @return qb
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
                        user.dateInscription,
                        user.username,
                        CONCAT(CONCAT(\'<a href="/?_switch_user=\', user.username), \'" target="_blank" class="btn btn-magenta fa fa-user" title="Simuler"></a>\') AS usernameSimulated,
                        user.pseudonymeForum,
                        user.email,
                        user.nom,
                        user.prenom,
                        user.alreadyBeAmbassadeur,
                        user.alreadyBeExpert,
                        refRegion.libelle as region,
                        user.roles,
                        refEtat.libelle as etat,
                        user.lock,
                        min(contractualisation.dateRenouvellement) as contra,
                        user.nbVisites,
                        GROUP_CONCAT(DISTINCT user_domaines.nom SEPARATOR \' - \') as domaines
            ')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.etat', 'refEtat')
            ->leftJoin('user.region', 'refRegion')
            ->leftJoin('user.contractualisations', 'contractualisation', Join::WITH, 'contractualisation.archiver = 0')
            ->join('user.domaines', 'user_domaines', Join::WITH, 'user_domaines.id IN (:domaines_ids)')
            ->groupBy('user.id')
            ->orderBy('user.dateInscription', 'DESC')
            ->addOrderBy('user.username')
        ->setParameters([
            'domaines_ids' => $domainesId,
        ]);

        return $qb;
    }

    /**
     * Override : Récupère les données Etablissement pour le grid sous forme de tableau.
     *
     * @return qb
     */
    public function getEtablissementForGrid()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id,
                     user.username,
                     user.nom,
                     user.prenom,
                     refRegion.libelle as region,
                     user.autreStructureRattachementSante,
                     user.archiver

            ')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.region', 'refRegion')
            ->where('user.autreStructureRattachementSante IS NOT NULL')
            ->orderBy('user.username');

        return $qb;
    }

    /**
     * Récupère les Etablissement pour l'export.
     *
     * @return qb
     */
    public function getEtablissementForExport($ids)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id,
                     user.username,
                     user.nom,
                     user.prenom,
                     refRegion.libelle as region,
                     user.autreStructureRattachementSante,
                     user.archiver
            ')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.region', 'refRegion')
            ->andWhere('user.autreStructureRattachementSante IS NOT NULL', 'user.id IN (:ids)')
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
            ->andWhere('user.roles LIKE :role', 'user.etablissementRattachementSante IS NOT NULL')
            ->andWhere('user.etablissementRattachementSante = :etablissementRattachementSante', 'user.id != :id')
            ->setParameter('id', $user->getId())
            ->setParameter('role', '%ROLE_ES_DIRECTION_GENERALE_5%')
            ->setParameter('etablissementRattachementSante', $user->getEtablissementRattachementSante());

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
            ->orderBy('user.nom', 'ASC')
        ;

        if (!is_null($domaine) && $domaine != 0) {
            $qb->innerJoin('user.connaissancesAmbassadeurs', 'domaines', join::WITH, 'domaines.domaine = :domaine')
                ->setParameter('domaine', $domaine)
                ->andWhere('domaines.connaissance IS NOT NULL');
        }

        return $qb;
    }

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
            ->orderBy('user.nom', 'ASC')
            ->addOrderBy('user.prenom', 'DESC');

        return $qb;
    }

    /**
     * Retourne la liste des utilisateurs possédant les roles demandés.
     *
     * @param array $role Le rôle demandé
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

        $qb->orderBy('user.nom', 'ASC')
                ->addOrderBy('user.prenom', 'DESC');

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
        $query = $this->createQueryBuilder('user');

        $query
            ->innerJoin('user.domaines', 'domaine', Expr\Join::WITH, $query->expr()->in('domaine.id', ':domaines'))
            ->setParameter('domaines', $domaines->toArray())
        ;

        return $query->getQuery()->getResult();
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
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des ambassadeurs
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
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des experts
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
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
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
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
     */
    public function getAdmins(array $criteres = [])
    {
        return $this->findByRole([Role::$ROLE_ADMIN_HN_LABEL, Role::$ROLE_ADMIN_LABEL], $criteres);
    }

    /**
     * Retourne une liste d'utilisateurs Cmsi.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     *
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
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
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
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
            ->addOrderBy('user.nom', 'ASC')
            ->addOrderBy('user.prenom', 'ASC')
        ;

        return $requete->getQuery()->getResult();
    }

    /**
     * Retourne une liste d'utilisateurs en fonction d'un rôle en respectant le retour d'un QB et non d'une liste d'utilisateur
     * ainsi que le public pour l'utilisateur dans des formType.
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
            ->addOrderBy('user.nom', 'ASC')
            ->addOrderBy('user.prenom', 'ASC')
        ;

        return $qb;
    }

    /**
     * Récupère les utilisateurs ayant répondues au questionnaire passé en paramètre.
     *
     * @param  int idQuestionnaire Identifiant du questionnaire
     *
     * @return result
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
            ->orderBy('user.nom', 'ASC')
            ->addOrderBy('user.prenom');

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
     * Récupère le nombre d'établissements connectés.
     *
     * @param Domaine $domaine
     *
     * @return int
     */
    public function getNbEtablissements(Domaine $domaine = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(distinct(user.etablissementRattachementSante))')
           ->from('HopitalNumeriqueUserBundle:User', 'user')
           ->where('user.etablissementRattachementSante IS NOT NULL')
        ;

        if (null !== $domaine) {
            $qb
                ->join('user.domaines', 'domaines', Join::WITH, 'domaines.id = :domaine')
                ->setParameter('domaine', $domaine)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Retourne la QueryBuilder avec les membres de la communauté de pratique.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe (optionnel) Groupe des membres
     *
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function getCommunautePratiqueMembresQueryBuilder(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe = null, Domaine $domaine = null, $membreId = null)
    {
        $query = $this->createQueryBuilder('user');

        $query
            ->select('user, esProfil, region, esStatut, typeActivite')
            ->leftJoin('user.profilEtablissementSante', 'esProfil')
            ->leftJoin('user.region', 'region')
            ->leftJoin('user.statutEtablissementSante', 'esStatut')
            ->leftJoin('user.typeActivite', 'typeActivite')
            ->andWhere('user.inscritCommunautePratique = :inscritCommunautePratique')
            ->setParameter('inscritCommunautePratique', true)
            ->andWhere('user.etat = :etat')
            ->setParameter('etat', User::ETAT_ACTIF_ID)
            ->addOrderBy('user.nom', 'ASC')
            ->addOrderBy('user.prenom', 'ASC')
            ->addOrderBy('user.id', 'ASC')
        ;

        if (null !== $groupe) {
            $query
                ->innerJoin('user.groupeInscription', 'groupeInscription', Join::WITH, 'groupeInscription.groupe = :groupe')
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
     * Retourne la QueryBuilder avec les membres d'un groupe de la communauté de pratique.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe Groupe des membres
     *
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder
     */
    public function getCommunautePratiqueUsersByGroupeQueryBuilder(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe)
    {
        $query = $this->createQueryBuilder('groupeUser');

        $query
            ->innerJoin('groupeUser.groupeInscription', 'groupeUserGroupe', Join::WITH, 'groupeUserGroupe.groupe = :groupeUserGroupe')
            ->setParameter('groupeUserGroupe', $groupe)
        ;

        return $query;
    }

    /**
     * Retourne les membres de la communauté de pratique n'appartenant pas à tel groupe.
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe Groupe
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Utilisateurs
     */
    public function findCommunautePratiqueMembresNotInGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe = null)
    {
        $query = $this->createQueryBuilder('user');

        $groupeUsers = $this->getCommunautePratiqueUsersByGroupeQueryBuilder($groupe)->getQuery()->getResult();

        $domaine = $this->getEntityManager()->getRepository('HopitalNumeriqueDomaineBundle:Domaine')->getDomaineFromHttpHost($_SERVER['SERVER_NAME'])->getQuery()->getOneOrNullResult();

        $query
            ->leftJoin('user.groupeInscription', 'groupeInscription')
            ->andWhere('user.inscritCommunautePratique = :inscritCommunautePratique')
            ->setParameter('inscritCommunautePratique', true)
            ->andWhere('user.etat = :etat')
            ->setParameter('etat', User::ETAT_ACTIF_ID)
            ->leftJoin('user.domaines', 'domaine')
            ->andWhere('domaine.url = :domaine')
            ->setParameter(':domaine', ($domaine) ? $domaine->getUrl() : null)
            ->addOrderBy('user.nom', 'ASC')
            ->addOrderBy('user.prenom', 'ASC')
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
     * @param int                                                $nombreMembres Nombre de membres à retourner
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $civilite      (optionnel) Civilité
     * @param array<\HopitalNumerique\UserBundle\Entity\User>    $ignores       (optionnel) Liste d'utilisateurs à ignorer
     *
     * @return array<\HopitalNumerique\UserBundle\Entity\User> Utilisateurs
     */
    public function findCommunautePratiqueRandomMembres(Domaine $domaine, $nombreMembres, Reference $civilite = null, array $ignores = null)
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

        if (null !== $civilite) {
            $query
                ->andWhere('user.civilite = :civilite')
                ->setParameter('civilite', $civilite)
            ;
        }

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
}
