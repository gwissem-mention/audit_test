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
     * @return array
     */
    public function getDatasForGrid()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('obj.id, obj.titre, obj.isInfraDoc, obj.isArticle, obj.nbVue, refEtat.libelle as etat, obj.dateCreation, obj.dateModification, obj.lock, obj.autodiags, user.email as lockedBy, refTypes.libelle as types')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.etat','refEtat')
            ->leftJoin('obj.types','refTypes')
            ->leftJoin('obj.lockedBy','user');
            
        return $qb;
    }

    /**
     * Récupère les objets pour l'export
     *
     * @return array
     */
    public function getDatasForExport( $ids )
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->where('obj.id IN (:ids)')
            ->orderBy('obj.titre', 'ASC')
            ->setParameter('ids', $ids);
            
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
    public function getObjetsByTypes( $types, $limit = 0 )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->where('refTypes.id IN (:types)','obj.etat = 3')
            ->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->isNull('obj.dateDebutPublication'),
                    $qb->expr()->lte('obj.dateDebutPublication', ':today')
                ),
                $qb->expr()->orx(
                    $qb->expr()->isNull('obj.dateFinPublication'),
                    $qb->expr()->gte('obj.dateFinPublication', ':today')
                )
            )
            ->setParameter('today', new \DateTime() )
            ->orderBy('obj.dateCreation', 'DESC')
            ->setParameter('types', $types );
        
        if( $limit !== 0 )
            $qb->setMaxResults($limit);

        return $qb;
    }

    /**
     * Retourne l'ensemble des productions actives
     */
    public function getProductionsActive()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('obj')
            ->from('HopitalNumeriqueObjetBundle:Objet', 'obj')
            ->leftJoin('obj.types','refTypes')
            ->innerJoin('refTypes.parent','refTypesParent')
            ->innerJoin('obj.etat','etat')
            ->where('refTypesParent.id = :idParent')->setParameter('idParent', 175 )
            ->andWhere('etat.id = :idActif')->setParameter('idActif', 3 )
            ->orderBy('obj.alias', 'ASC');
        
        return $qb;
    }
}