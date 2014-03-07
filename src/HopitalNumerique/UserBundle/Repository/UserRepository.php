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
            ->orderBy('user.username');
        
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