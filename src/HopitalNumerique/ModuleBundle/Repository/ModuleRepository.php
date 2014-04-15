<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ModuleRepository
 */
class ModuleRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     */
    public function getDatasForGrid()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('mod.id, mod.titre, refEtat.libelle as statut, productions.titre as prod_titre')
            ->from('HopitalNumeriqueModuleBundle:Module', 'mod')
            ->leftJoin('mod.statut','refEtat')
            ->leftJoin('mod.productions','productions')
            ->orderBy('mod.titre');
        
        return $qb;
    }
}