<?php

namespace HopitalNumerique\ExpertBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * EvenementExpertRepository
 */
class EvenementExpertRepository extends EntityRepository
{
    /**
     * Récupère les données du grid sous forme de tableau correctement formaté
     *
     * @return Query Builder
     */
    public function getDatasForGrid($condition = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('eve.id, nomRef.libelle as nom, eve.date, eve.nbVacation')
            ->from('HopitalNumeriqueExpertBundle:EvenementExpert', 'eve')
            ->leftJoin('eve.nom', 'nomRef')
            ->leftJoin('eve.activite', 'act')
            ->where('act.id = :activite')
            ->setParameter('activite', $condition->value)
            ->orderBy('eve.date', 'DESC');
            
        return $qb;
    }
}
