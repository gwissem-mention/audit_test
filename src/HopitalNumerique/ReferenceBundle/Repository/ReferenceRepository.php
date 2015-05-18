<?php

namespace HopitalNumerique\ReferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

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
    public function getArbo( $unlockedOnly = false, $fromDictionnaire = false, $fromRecherche = false, $domaineIds = array() )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, ref.code, par.id as parent, ref.order')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.parent','par');

        if(count($domaineIds) !== 0)
        {
            $qb->leftJoin('ref.domaines', 'domaine')
                ->andWhere('domaine.id IN (:domainesId)')
                ->setParameter('domainesId', $domaineIds);
        }
            
        if( $unlockedOnly )
        {
            $qb->andWhere('ref.lock = 0');
        }

        if( $fromDictionnaire )
        {
            $qb->andWhere('ref.dictionnaire = 1');
        }

        if( $fromRecherche )
        {
            $qb->andWhere('ref.recherche = 1');
        }

        $qb->orderBy('ref.parent, ref.code, ref.order');

        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref.id, ref.libelle, ref.code, ref.dictionnaire, ref.recherche, ref.lock, ref.order, refEtat.libelle as etat, refParent.id as idParent, domaine.nom as domaineNom')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.etat','refEtat')
            ->leftJoin('ref.parent','refParent')
            ->leftJoin('ref.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            // ->groupBy('ref')
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
        $qb->select('ref.id, ref.libelle, ref.code, ref.dictionnaire, ref.recherche, ref.lock, ref.order, refEtat.libelle as etat, refParent.id as idParent, domaine.nom as domaineNom')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.etat','refEtat')
            ->leftJoin('ref.parent','refParent')
            ->leftJoin('ref.domaines', 'domaine')
            ->where('ref.id IN (:ids)')
            ->orderBy('ref.code, ref.order')
            ->setParameter('ids', $ids);
            
        return $qb;
    }

    /**
     * Récupère les références ayant un domaine
     *
     * @return [type]
     */
    public function getReferencesWithDomaine()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.domaines','domaine')
                ->where($qb->expr()->isNotNull('domaine.id'))
            ->orderBy('domaine.nom');
            
        return $qb;
    }



    /**
     * Récupère les différents ref_code des références
     *
     * @return QueryBuilder
     */
    public function getAllRefCode(array $domainesQuestionnaireId)
    {
        //TODO : check si la fonction est utilisée autre part que pour les questions + passées les domaines du questionnaire pour filtrer
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ref')
            ->from('HopitalNumeriqueReferenceBundle:Reference', 'ref')
            ->leftJoin('ref.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesQuestionnaireId)
            ->groupBy('ref.code')
            ->orderBy('ref.code');
            
        return $qb;
    }
}