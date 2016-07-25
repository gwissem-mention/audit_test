<?php

namespace HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute\Weight;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class ValueRepository extends EntityRepository
{
    public function getValuesAndWeight(Synthesis $synthesis)
    {
        $qb = $this->createQueryBuilder('v');
        $qb
            ->select(
                'synthesis.id',
                'container.id as container_id',
                'v.value',
                'weight.weight',
                'attribute.type',
                'MIN(options.value) as lowest',
                'MAX(options.value) as highest'
            )
            ->join('v.entry', 'entry')
            ->join('entry.synthesis', 'synthesis')
            ->join('v.attribute', 'attribute')
            ->leftJoin('attribute.options', 'options', Join::WITH, 'options.value != -1')
            ->join(Weight::class, 'weight', Join::WITH, 'attribute.id = weight.attribute')
            ->join('weight.container', 'container')
            ->where('synthesis.id = :synthesis_id')
            ->groupBy('v.id')
            ->addGroupBy('container.id')
            ->setParameters([
                'synthesis_id' => $synthesis->getId(),
            ]);

        return $qb->getQuery()->getResult();
    }
}
