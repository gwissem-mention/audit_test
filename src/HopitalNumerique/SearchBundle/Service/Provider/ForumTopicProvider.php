<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

/**
 * Content type Provider
 */
class ForumTopicProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $queryBuilder = $this->repository->createQueryBuilder('topic');
        $queryBuilder
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
