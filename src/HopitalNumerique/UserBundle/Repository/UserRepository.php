<?php

namespace HopitalNumerique\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

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
}