<?php

namespace HopitalNumerique\AideBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\AideBundle\Entity\Aide;

/**
 * AideRepository
 */
class AideRepository extends EntityRepository
{

    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('aide.id, aide.route, aide.libelle,')
            ->from('HopitalNumeriqueAideBundle:Aide', 'aide');

        return $qb;
    }
}