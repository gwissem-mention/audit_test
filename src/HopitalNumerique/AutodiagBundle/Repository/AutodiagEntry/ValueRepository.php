<?php

namespace HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute\Weight;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class ValueRepository extends EntityRepository
{
    public function getValuesAndWeight(array $entries)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select(
                'container.id as container_id',
                'v.value',
                'weight.weight',
                'attribute.type',
                'MIN(options.value) as lowest',
                'MAX(options.value) as highest'
            )
            ->join('v.entry', 'entry')
            ->join('v.attribute', 'attribute')
            ->leftJoin('attribute.options', 'options', Join::WITH, 'options.value != -1')
            ->join(Weight::class, 'weight', Join::WITH, 'attribute.id = weight.attribute')
            ->join('weight.container', 'container')
            ->where('entry.id IN (:entries_id)')
            ->groupBy('v.id')
            ->addGroupBy('container.id')
            ->setParameters([
                'entries_id' => array_map(function (AutodiagEntry $entry) {
                    return $entry->getId();
                }, $entries),
            ]);

        return $qb->getQuery()->getResult();
    }
}
