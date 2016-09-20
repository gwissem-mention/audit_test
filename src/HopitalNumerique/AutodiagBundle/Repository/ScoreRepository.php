<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;

class ScoreRepository extends EntityRepository
{
    public function getScore($synthesisId, $containerId)
    {
        $qb = $this->createQueryBuilder('score');
        $qb
            ->select('score.score')
            ->where('score.container = :container_id')
            ->andWhere('score.synthesis = :synthesis_id')
            ->setParameters([
                'synthesis_id' => $synthesisId,
                'container_id' => $containerId,
            ]);

        return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    public function getReferenceScores(Container $container)
    {
        $attributesCountQb = $this->getEntityManager()->createQueryBuilder();
        $attributesCountQb
            ->select('count(attribute.id)')
            ->from('HopitalNumeriqueAutodiagBundle:Autodiag\Attribute', 'attribute')
            ->join(Attribute\Weight::class, 'weight', Join::WITH, 'weight.attribute = attribute.id')
            ->join('weight.container', 'container')
            ->where(
                $attributesCountQb->expr()->in('container.id', $container->getNestedContainerIds())
            )
        ;
        $attributesCount = $attributesCountQb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);


        $qb = $this->createQueryBuilder('score');
        $qb
            ->select('score.score')
            ->join('score.synthesis', 'synthesis')
            ->join('synthesis.entries', 'entries', Join::WITH, 'entries.copy = FALSE')
            ->where(
                $qb->expr()->in('score.container', $container->getNestedContainerIds())
            )
        ;

        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        return array_map(function ($score) {
            return $score['score'];
        }, $result);
    }
}
