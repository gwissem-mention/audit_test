<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\Query\Expr\Join;

/**
 * Group type provider
 */
class CDPMessageProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $queryBuilder = $this->repository->createQueryBuilder('message');
        $queryBuilder
            ->join('message.discussion', 'discussion', Join::WITH, 'discussion.public = TRUE')
            ->join('discussion.domains', 'domain', Join::WITH, 'domain.slug = :domainSlug')
            ->setParameters([
                'domainSlug' => $this->domaineSlug,
            ])
        ;

        return $queryBuilder;
    }
}
