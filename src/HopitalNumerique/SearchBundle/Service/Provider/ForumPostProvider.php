<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

/**
 * Content type Provider
 */
class ForumPostProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $queryBuilder = $this->repository->createQueryBuilder('post');
        $queryBuilder
            ->join('post.topic', 'topic')
            ->join('topic.board', 'board')
            ->join('board.category', 'category')
            ->join('category.forum', 'forum')
            ->join('forum.domain', 'domain')
            ->andWhere('domain.slug = :domaineSlug')
            ->setParameters([
                'domaineSlug' => $this->domaineSlug,
            ])
        ;

        return $queryBuilder;
    }
}
