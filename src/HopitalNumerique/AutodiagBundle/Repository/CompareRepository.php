<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Compare;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class CompareRepository extends EntityRepository
{
    public function findFromOrigin(Synthesis $synthesis, Synthesis $reference)
    {
        $qb = $this->createQueryBuilder('compare');
        $qb
            ->join('compare.synthesis', 'synthesis', Join::WITH, 'synthesis.createdFrom = :synthesis_id')
            ->join('compare.reference', 'reference', Join::WITH, 'reference.createdFrom = :reference_id')
            ->setParameters([
                'synthesis_id' => $synthesis->getId(),
                'reference_id' => $reference->getId(),
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Synthesis $synthesis
     *
     * @return Compare[]
     */
    public function findRelatedToSynthesis(Synthesis $synthesis)
    {
        $qb = $this->createQueryBuilder('compare');
        $qb
            ->join('compare.synthesis', 'synthesis')
            ->join('compare.reference', 'reference')
            ->where(
                $qb->expr()->orX(
                    'synthesis.createdFrom = :synthesis_id',
                    'reference.createdFrom = :synthesis_id'
                )
            )
            ->setParameters([
                'synthesis_id' => $synthesis->getId(),
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
