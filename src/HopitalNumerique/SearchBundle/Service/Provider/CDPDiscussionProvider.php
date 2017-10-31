<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\Query\Expr\Join;

/**
 * Group type provider
 */
class CDPDiscussionProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $queryBuilder = $this->repository->createQueryBuilder('discussion');
        $queryBuilder
            ->join('discussion.domains', 'domain', Join::WITH, 'domain.slug = :domainSlug')
            ->setParameters([
                'domainSlug' => $this->domaineSlug,
            ])
            ->andWhere('discussion.public = TRUE')
        ;

        return $queryBuilder;
    }
}
