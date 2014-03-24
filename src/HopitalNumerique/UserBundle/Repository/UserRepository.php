<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Nodevo\RoleBundle\Entity\Role;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid()
    {        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id, 
                        user.dateInscription, 
                        user.username, 
                        user.email, 
                        user.nom, 
                        user.prenom, 
                        refRegion.libelle as region, 
                        user.roles,
                        refEtat.libelle as etat, 
                        user.lock, 
                        min(contractualisation.dateRenouvellement) as contra
            ')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.etat','refEtat')
            ->leftJoin('user.region','refRegion')
            ->leftJoin('user.contractualisations', 'contractualisation', 'WITH', 'contractualisation.archiver = 0')
            ->groupBy('user')
            ->orderBy('user.dateInscription', 'DESC')
            ->addOrderBy('user.username');
        
        return $qb;
    }

    /**
     * Retourne la liste des établissement 'Autres'
     *
     * @return QueryBuilder
     */
    public function getDatasForGridEtablissement()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user.id, user.username, user.nom, user.prenom, refRegion.libelle as region, user.archiver, user.autreStructureRattachementSante')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.region','refRegion')
            ->where('user.autreStructureRattachementSante IS NOT NULL ')
            ->orderBy('user.username');
        
        return $qb;
    }

    /**
     * On cherche a savoir si un user existe avec le role et la région de l'user modifié
     *
     * @param User $user L'utilisateur modifié
     *
     * @return QueryBuilder
     */
    public function userExistForRoleArs( $user )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->andWhere('user.region = :region', 'user.roles LIKE :role')
            ->setParameter('role', '%ROLE_ARS_CMSI_4%')
            ->setParameter('region', $user->getRegion() );
        
        if( !is_null($user->getId()) ){
            $qb->andWhere('user.id != :id')
               ->setParameter('id', $user->getId() );
        }

        return $qb;
    }

    /**
     * On cherche a savoir si un user existe avec le role et la région de l'user modifié
     *
     * @param User $user L'utilisateur modifié
     *
     * @return QueryBuilder
     */
    public function userExistForRoleDirection( $user )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->andWhere('user.roles LIKE :role', 'user.etablissementRattachementSante IS NOT NULL')
            ->andWhere('user.etablissementRattachementSante = :etablissementRattachementSante', 'user.id != :id')
            ->setParameter('id', $user->getId() )
            ->setParameter('role', '%ROLE_ES_DIRECTION_GENERALE_5%')
            ->setParameter('etablissementRattachementSante', $user->getEtablissementRattachementSante() );

        return $qb;
    }

    /**
     * Retourne la liste des ambassadeurs de la région $region
     *
     * @param Reference $region  La région filtrée
     * @param integer   $domaine Le domaine
     *
     * @return QueryBuilder
     */
    public function getAmbassadeursByRegionAndDomaine( $region, $domaine )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->andWhere('user.roles LIKE :role','user.region = :region')
            ->andWhere('user.enabled = 1')
            ->setParameter('region', $region)
            ->setParameter('role', '%ROLE_AMBASSADEUR_7%');

        if( !is_null($domaine) && $domaine != 0 ){
            $qb->leftJoin('user.domaines','domaines')
                ->andWhere('domaines.id = :domaine')
                ->setParameter('domaine', $domaine );
        }

        return $qb;
    }

    public function getUsersGroupeEtablissement($criteres)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
        ->from('HopitalNumeriqueUserBundle:User', 'user')
        ->andWhere('user.roles LIKE :role')
        ->andWhere('user.enabled = 1')
        ->setParameter('role', '%ROLE_ES_8%');
        
        foreach ($criteres as $critereChamp => $critereValeur)
        {
            if (is_array($critereValeur))
            {
                $qb->andWhere('user.'.$critereChamp.' IN ('.implode(',', $critereValeur).')');
            }
            else
            {
                $qb->andWhere('user.'.$critereChamp.' = :'.$critereChamp)
                    ->setParameter($critereChamp, $critereValeur);
            }
        }

        return $qb;
    }

    /**
     * Retourne la liste des ambassadeurs de la région et de la publication
     *
     * @param Reference $region La région filtrée
     * @param Objet     $objet  La publication
     *
     * @return QueryBuilder
     */
    public function getAmbassadeursByRegionAndProduction( $region, $objet )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->leftJoin('user.objets','objets')
            ->andWhere('user.roles LIKE :role','user.enabled = 1')
            ->andWhere('objets.id = :objet')
            ->setParameter('objet', $objet )
            ->setParameter('role', '%ROLE_AMBASSADEUR_7%');

        if( $region ){
            $qb->andWhere('user.region = :region')
                ->setParameter('region', $region);
        }

        return $qb;
    }
    
    /**
     * Retourne la liste des utilisateurs possédant le role demandé
     *
     * @param string $role Le rôle demandé
     *
     * @return QueryBuilder
     */
    public function findUsersByRole( $role )
    {
        $qb = $this->_em->createQueryBuilder();
    
        $qb->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->where('user.roles LIKE :role')
            ->setParameter('role', '%'.$role.'%');

        return $qb;
    }

    /**
     * Retourne le premier utilisateur correspondant au role et à la région demandés
     *
     * @param string $role      Le rôle demandé
     * @param int    $idRegion  Region demandée
     *
     * @return QueryBuilder
     */
    public function findUsersByRoleAndRegion( $idRegion, $role )
    {
        $qb = $this->_em->createQueryBuilder();
    
        $qb->select('user')
        ->from('HopitalNumeriqueUserBundle:User', 'user')
        ->where('user.roles LIKE :role')
        ->setParameter('role', '%'.$role.'%')
        ->andWhere('user.region = :idRegion')
        ->setParameter('idRegion', $idRegion)
        ->andWhere('user.enabled = 1');
    
        return $qb;
    }


























    
    /**
     * Retourne un unique CMSI.
     *
     * @param array $criteres Filtres à appliquer sur la liste
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
     * @return \HopitalNumerique\UserBundle\Entity\User|null Un directeur si trouvé, sinon NIL
     */
    public function getDirecteur(array $criteres)
    {
        return $this->findOneByRole(Role::$ROLE_DIRECTEUR_LABEL, $criteres);
    }
    /**
     * Retourne un unique utilisateur en fonction d'un rôle.
     *
     * @param string $role Label du rôle sur lequel filtrer
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User Un utilisateur si trouvé, sinon NIL
     */
    private function findOneByRole($role, array $criteres)
    {
        $utilisateurs = $this->findByRole($role, $criteres);
        if (count($utilisateurs) > 0)
            return $utilisateurs[0];
        return null;
    }
    /**
     * Retourne une liste de CMSIs.
     * 
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des CMSIs
     */
    public function getCmsis(array $criteres)
    {
        return $this->findByRole(Role::$ROLE_CMSI_LABEL, $criteres);
    }
    /**
     * Retourne une liste de directeurs.
     * 
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des directeurs
     */
    public function getDirecteurs(array $criteres)
    {
        return $this->findByRole(Role::$ROLE_DIRECTEUR_LABEL, $criteres);
    }
    /**
     * Retourne une liste d'ambassadeurs.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des ambassadeurs
     */
    public function getAmbassadeurs(array $criteres)
    {
        return $this->findByRole(Role::$ROLE_AMBASSADEUR_LABEL, $criteres);
    }
    /**
     * Retourne une liste d'utilisateurs en fonction d'un rôle.
     *
     * @param string $role Label du rôle sur lequel filtrer
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
     */
    private function findByRole($role, array $criteres)
    {
        $requete = $this->_em->createQueryBuilder();
    
        $requete->select('user')
            ->from('HopitalNumeriqueUserBundle:User', 'user')
            ->where('user.roles LIKE :role')
                ->setParameter('role', '%'.$role.'%');
    
        foreach ($criteres as $critereChamp => $critereValeur)
        {
            $requete->andWhere('user.'.$critereChamp.' = :'.$critereChamp)
                ->setParameter($critereChamp, $critereValeur);
        } 

        return $requete->getQuery()->getResult();
    }
}