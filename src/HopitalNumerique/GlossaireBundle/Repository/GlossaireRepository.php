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
}
