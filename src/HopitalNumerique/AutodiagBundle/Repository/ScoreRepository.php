<?php

namespace HopitalNumerique\AutodiagBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use HopitalNumerique\AutodiagBundle\Entity\Synthesis;

class ScoreRepository extends EntityRepository
{
    public function getScore($synthesisId, $containerId)
    {
        $qb = $this->createSynthesisScoreQueryBuilder($synthesisId);
        $qb
            ->andWhere('score.container = :container_id')
            ->setParameter('container_id', $containerId)
        ;

        return $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    public function getScores($synthesisId)
    {
        $qb = $this->createSynthesisScoreQueryBuilder($synthesisId);
        $qb
            ->addSelect('container.id')
            ->join('score.container', 'container')
        ;

        $result = $qb->getQuery()->getArrayResult();
        $data = [];
        foreach ($result as $score) {
            $data[$score['id']] = [
                'score' => $score['score'],
                'min' => $score['min_score'],
                'max' => $score['max_score'],
            ];
        }
        return $data;
    }

    protected function createSynthesisScoreQueryBuilder($synthesisId)
    {
        $qb = $this->createQueryBuilder('score');
        $qb
            ->select('score.score', 'score.min as min_score', 'score.max as max_score')
            ->where('score.synthesis = :synthesis_id')
            ->setParameter('synthesis_id', $synthesisId)
        ;
        return $qb;
    }

    /**
     * Get score for references
     *
     * @param Container $container
     * @return array
     */
    public function getReferenceScores(Container $container)
    {
        $qb = $this->createQueryBuilder('score');
        $qb
            ->select('score.score')
            ->join('score.synthesis', 'synthesis')
            ->join('synthesis.entries', 'entries', Join::WITH, 'entries.copy = FALSE')
            ->where(
                $qb->expr()->eq('score.container', $container->getId())
            )
            ->andWhere('score.score IS NOT NULL')
            ->andWhere('score.complete = TRUE')
            ->groupBy('entries.id')
        ;

        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        return array_map(function ($score) {
            return $score['score'];
        }, $result);
    }

    public function getBoundariesFromSyntheses(Container $container, $synthesesIds)
    {
        $qb = $this->createQueryBuilder('score');
        $qb
            ->select(
                'MIN(score.min) as min_score',
                'MAX(score.max) as max_score'
            )
            ->where(
                $qb->expr()->eq('score.container', $container->getId())
            )
            ->andWhere(
                $qb->expr()->in('score.synthesis', $synthesesIds)
            )
        ;

        $result = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        return $result;
    }
}
