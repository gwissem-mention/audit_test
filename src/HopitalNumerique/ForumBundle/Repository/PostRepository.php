<?php

namespace HopitalNumerique\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TopicRepository.
 */
class PostRepository extends EntityRepository
{
    /**
     * Retrieves the first post of each board topic.
     *
     * @param int $boardId
     *
     * @return array
     */
    public function getFirstPostsFromBoard($boardId)
    {
        $qb = $this->createQueryBuilder('post');

        $qb
            ->leftJoin('post.topic', 'topic')
            ->leftJoin('topic.board', 'board')
            ->where('board.id = :boardId')
            ->setParameter('boardId', $boardId)
            ->groupBy('topic.id')
            ->orderBy('post.createdDate', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }
}
