<?php

namespace HopitalNumerique\SearchBundle\Service\Provider;

use Doctrine\ORM\Query\Expr\Join;

/**
 * Content type Provider
 */
class ContentProvider extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createQueryBuidler()
    {
        $queryBuilder = $this->repository->createQueryBuilder('content');
        $queryBuilder
            ->join('content.objet', 'object')
            ->leftJoin('content.domaines', 'content_domaine')
            ->join('object.domaines', 'object_domaine')
            ->andWhere('object.isArticle = false')
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'content_domaine.slug = :domaineSlug',
                    $queryBuilder->expr()->andX(
                        'content_domaine.slug IS NULL',
                        'object_domaine.slug = :domaineSlug'
                    )
                )
            )
            ->setParameters([
                'domaineSlug' => $this->domaineSlug,
            ])
        ;

        return $queryBuilder;
    }
}
