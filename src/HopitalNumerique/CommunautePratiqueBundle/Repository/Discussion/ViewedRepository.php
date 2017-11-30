<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Repository\Discussion;

use Doctrine\ORM\Query\Expr\Join;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Viewed;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Class ViewedRepository
 */
class ViewedRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $since
     * @param $limit
     * @param Groupe|null $group
     *
     * @return array
     */
    public function getMostViewed($since, $limit, Groupe $group = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb
            ->select('count(view.id) as views', 'discussion.id')
            ->from(Discussion::class, 'discussion')
            ->leftJoin(Viewed::class, 'view', Join::WITH, 'view.discussion = discussion.id')
            ->andWhere(
                $qb->expr()->orX(
                    'view.viewDate >= :since',
                    'view.id IS NULL'
                )
            )
            ->setParameter('since', $since)
        ;

        if ($group) {
            $qb
                ->join('discussion.groups', 'cdpGroup', Join::WITH, 'cdpGroup.id = :group')
                ->setParameter('group', $group)
            ;
        } else {
            $qb->andWhere('discussion.public = TRUE');
        }

        $views = $qb
            ->groupBy('discussion.id')
            ->orderBy('views', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        $viewsFiltered = array_slice($views, 0, ceil((count($views)/100)*$limit));

        $results = [];
        foreach ($viewsFiltered as $view) {
            $results[$view['id']] = $view['views'];
        }

        $results = array_filter($results, function ($result) {
            return $result > 0;
        });

        return $results;
    }
}
