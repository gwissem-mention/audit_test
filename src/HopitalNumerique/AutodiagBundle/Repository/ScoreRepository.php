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
            $data[$score['id']] = $score['score'];
        }
        return $data;
    }

    protected function createSynthesisScoreQueryBuilder($synthesisId)
    {
        $qb = $this->createQueryBuilder('score');
        $qb
            ->select('score.score')
            ->where('score.synthesis = :synthesis_id')
            ->setParameter('synthesis_id', $synthesisId)
        ;
        return $qb;
    }

    /**
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
//        if ($container->getId() == 731) {
//            dump($container);
//            dump($qb->getQuery()->getSQL());
//            dump($qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY));die;
//        }


        $result = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        return array_map(function ($score) {
            return $score['score'];
        }, $result);
    }
}
