<?php

namespace HopitalNumerique\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * BoardRepository.
 */
class BoardRepository extends EntityRepository
{
    public function findAll()
    {
        $qb = $this->createQueryBuilder('board');

        $qb
            ->innerJoin('board.category', 'category')
            ->addSelect('category')
            ->innerJoin('category.forum', 'forum')
            ->addSelect('forum')
            ->orderBy('forum.name', 'ASC')
            ->addOrderBy('category.listOrderPriority', 'ASC')
            ->addOrderBy('board.listOrderPriority', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }
}
