<?php

namespace HopitalNumerique\ModuleBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ModuleRepository
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ModuleRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
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