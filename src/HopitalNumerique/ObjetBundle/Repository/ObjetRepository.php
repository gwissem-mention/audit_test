<?php

namespace HopitalNumerique\ObjetBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ObjetRepository
 */
class ObjetRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return QueryBuilder
     */
    public function getDatasForGrid()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj.id, obj.titre, obj.isInfraDoc, obj.isArticle,  refEtat.libelle as etat, obj.dateCreation, obj.dateModification, obj.lock, user.email as lockedBy, refTypes.libelle as types')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.etat','refEtat')
            ->leftJoin('obj.types','refTypes')
            ->leftJoin('obj.lockedBy','user');
            
        return $qb;
    }

    /**
     * Récupère les données du grid pour un ambassadeur sous forme de tableau correctement formaté
     *
     * @return QueryBuilder
     */
    public function getDatasForGridAmbassadeur( $idAmbassadeur )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj.id, obj.titre, refTypes.libelle as types, refUser.id as user')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->leftJoin('obj.ambassadeurs','refUser')
            ->where('refUser.id = :idAmbassadeur')
            ->setParameter('idAmbassadeur', $idAmbassadeur->value );
        
        return $qb;
    }
    
    /**
     * Récupère les objets pour un ambassadeur passé en param
     *
     * @return QueryBuilder
     */
    public function getObjetsByAmbassadeur( $idAmbassadeur )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->leftJoin('obj.ambassadeurs','refUser')
            ->where('refUser.id = :idAmbassadeur')
            ->setParameter('idAmbassadeur', $idAmbassadeur );
        
        return $qb;
    }

    /**
     * Retourne la liste des objets selon le/les types
     *
     * @param array $types Les types à filtrer
     *
     * @return QueryBuilder
     */
    public function getObjetsByTypes( $types )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->where('refTypes.id IN (:types)')
            ->setParameter('types', $types );
        
        return $qb;
    }
}