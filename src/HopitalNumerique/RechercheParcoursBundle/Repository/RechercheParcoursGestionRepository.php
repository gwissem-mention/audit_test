<?php

namespace HopitalNumerique\RechercheParcoursBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RechercheParcoursGestionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RechercheParcoursGestionRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid($domainesIds, $condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('rpg.id, rpg.nom, domaine.nom as domaineNom')
            ->from('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion', 'rpg')
            ->leftJoin('rpg.domaines', 'domaine')
                ->where($qb->expr()->orX(
                    $qb->expr()->in('domaine.id', ':domainesId'),
                    $qb->expr()->isNull('domaine.id')
                ))
                ->setParameter('domainesId', $domainesIds)
            ->orderBy('rpg.nom');
            
        return $qb;
    }
}