<?php

namespace HopitalNumerique\GlossaireBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * GlossaireRepository
 */
class GlossaireRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('glo.id, glo.mot, glo.intitule, glo.sensitive, domaine.nom as domaineNom')
            ->from('HopitalNumeriqueGlossaireBundle:Glossaire', 'glo')
            ->leftJoin('glo.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            ->orderBy('glo.mot');
            
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
        $qb->select('glo.id, glo.mot, glo.intitule, glo.sensitive, glo.description, etat.libelle as etatLibelle, domaine.nom as domaineNom')
            ->from('HopitalNumeriqueGlossaireBundle:Glossaire', 'glo')
            ->leftJoin('glo.domaines', 'domaine')
            ->leftJoin('glo.etat', 'etat')
            ->where('glo.id IN (:ids)')
            ->orderBy('glo.mot')
            ->setParameter('ids', $ids);
            
        return $qb;
    }

    /**
     * Récupération de tout les glossaires ayant un domaine
     *
     * @return [type]
     */
    public function getAllGlossaireDomaineNotNull()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('glo')
            ->from('HopitalNumeriqueGlossaireBundle:Glossaire', 'glo')
            ->leftJoin('glo.domaines', 'domaine')
            ->where('domaine.id IS NOT NULL')
            ->orderBy('domaine.id');
            
        return $qb;
    }
}