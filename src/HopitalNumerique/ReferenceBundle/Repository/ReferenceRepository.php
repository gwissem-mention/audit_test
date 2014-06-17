<?php

namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ReferenceRepository
 */
class ReferenceRepository extends EntityRepository
{
    /**
     * Récupère tous les items de l'arborescence référence dans le bon ordre
     *
     * @return array
     */
    public function getArbo( $unlockedOnly = false, $fromDictionnaire = false, $fromRecherche = false )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, ref.code, par.id as parent, ref.order')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.parent','par')
            ->orderBy('ref.parent, ref.code, ref.order');
            
        if( $unlockedOnly )
            $qb->andWhere('ref.lock = 0');

        if( $fromDictionnaire )
            $qb->andWhere('ref.dictionnaire = 1');

        if( $fromRecherche )
            $qb->andWhere('ref.recherche = 1');

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, ref.code, ref.dictionnaire, ref.recherche, ref.lock, ref.order, refEtat.libelle as etat, refParent.id as idParent')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.etat','refEtat')
            ->leftJoin('ref.parent','refParent')
            ->orderBy('ref.code, ref.order');
            
        return $qb;
    }

    /**
     * Récupère les données pour l'export CSV
     *
     * @return QueryBuilder
     */
    public function getDatasForExport( $ids )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, ref.code, ref.dictionnaire, ref.recherche, ref.lock, ref.order, refEtat.libelle as etat, refParent.id as idParent')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.etat','refEtat')
            ->leftJoin('ref.parent','refParent')
            ->where('ref.id IN (:ids)')
            ->orderBy('ref.code, ref.order')
            ->setParameter('ids', $ids);
            
        return $qb;
    }
}