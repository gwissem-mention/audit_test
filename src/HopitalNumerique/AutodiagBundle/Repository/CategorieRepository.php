<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CategorieRepository
 */
class CategorieRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid( $condition )
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('cat.id as idCat, cat.title, cat.note, out.id as id')
            ->from('HopitalNumeriqueAutodiagBundle:Categorie', 'cat')
            ->leftJoin('cat.outil','out')
            ->where('cat.'.$condition->field.' = '.$condition->value)
            ->orderBy('cat.title','asc');
            
        return $qb;
    }
}